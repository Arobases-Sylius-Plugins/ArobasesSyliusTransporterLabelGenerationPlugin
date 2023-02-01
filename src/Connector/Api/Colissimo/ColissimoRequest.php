<?php

declare(strict_types=1);

namespace Arobases\SyliusTransporterLabelGenerationPlugin\Connector\Api\Colissimo;

use Arobases\SyliusTransporterLabelGenerationPlugin\Entity\Label;
use Arobases\SyliusTransporterLabelGenerationPlugin\Entity\LabelItem;
use Arobases\SyliusTransporterLabelGenerationPlugin\Entity\Transporter;
use Sylius\Component\Core\Model\Adjustment;
use Sylius\Component\Core\Model\Channel;
use Sylius\Component\Core\Model\Order;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class ColissimoRequest extends AbstractController
{
    public function generateLabel(Channel $channel, Label $label, Transporter $transporter, string $outputPrintingType, string $depositDate): array
    {
        $order = $label->getRelatedOrder();
        $adjustmentShipping = $order->getAdjustments("shipping");
        $shippingCosts = 0;
        /** @var Adjustment $adjustment */
        foreach ($adjustmentShipping as $adjustment) {
            $shippingCosts += $adjustment->getAmount();
        }
        $serviceCode = $order->getShipments()->last()->getMethod()?->getTransporterCode();
        if (!$serviceCode) {
            $serviceCode = 'DOM';
        }

        $params = [
            'contractNumber' => $transporter->getAccountNumber(),
            'password' => $transporter->getPassword(),
            'outputFormat' => ['x' => 0, 'y' => 0, 'outputPrintingType' => $outputPrintingType],
            'letter' => [
                'service' => [
                    'productCode' => $serviceCode,
                    'depositDate' => $depositDate,
                    'orderNumber' => $order->getNumber(),
                ],
                'parcel' => ['weight' => $label->getTotalWeight()],
                'sender' => [
                    'address' => [
                        'companyName' => $channel->getShopBillingData()->getCompany(),
                        'line2' => $channel->getShopBillingData()->getStreet(),
                        'countryCode' => $channel->getShopBillingData()->getCountryCode(),
                        'city' => $channel->getShopBillingData()->getCity(),
                        'zipCode' => $channel->getShopBillingData()->getPostcode(),
                    ],
                ],
                'addressee' => [
                    'address' => [
                        'lastName' => $order->getCustomer()->getLastName(),
                        'firstName' => $order->getCustomer()->getFirstName(),
                        'line2' => $order->getShippingAddress()->getStreet(),
                        'countryCode' => $order->getShippingAddress()->getCountryCode(),
                        'city' => $order->getShippingAddress()->getCity(),
                        'zipCode' => $order->getShippingAddress()->getPostcode(),
                    ],
                ],
            ],
        ];

        // pickup point
        if ($serviceCode === 'A2P' || $serviceCode === 'BPR' || $serviceCode === 'ACP' || $serviceCode === 'CDI' || $serviceCode === 'CMT' || $serviceCode === 'BDP' || $serviceCode === 'PCS') {
            $params['letter']['parcel']['pickupLocationId'] = $order->getShippingAddress()->getPickupPointId();
        }
        if ($serviceCode === 'A2P' || $serviceCode === 'BPR' || $serviceCode === 'BDP' || $serviceCode === 'CMT' || $serviceCode === 'CORE') {
            $params['letter']['service']['commercialName'] = $order->getShippingAddress()->getCompany();
        }
        if ($serviceCode === 'A2P' || $serviceCode === 'BPR') {
            $params['letter']['addressee']['address']['mobileNumber'] = $order->getShippingAddress()->getPhoneNumber() ? $order->getShippingAddress()->getPhoneNumber() : $order->getBillingAddress()->getPhoneNumber();
            $params['letter']['addressee']['address']['email'] = $order->getCustomer()->getEmail();
        }
        if ($serviceCode === 'CORE') {
            $params['letter']['addressee'] = [
                'addresseeParcelRef' => '1',
                'address' => [
                    'companyName' => $order->getShippingAddress()->getCompany(),
                    'line2' => $order->getShippingAddress()->getStreet(),
                    'countryCode' => $order->getShippingAddress()->getCountryCode(),
                    'city' => $order->getShippingAddress()->getCity(),
                    'zipCode' => $order->getShippingAddress()->getPostcode(),
                ],
            ];
        }
        define('SERVER_NAME', 'https://ws.colissimo.fr');

        if ($serviceCode === 'COM' || $serviceCode === 'CDS') {
            // Add Article to CN23 mandatory to Internationnal
            $phoneNumber = $channel->getContactPhoneNumber() ? $channel->getContactPhoneNumber() : "0600000000";
            $email = $channel->getContactEmail() ? $channel->getContactEmail() : "test@gmail.com";
            $addresseePhoneNumber = $order->getShippingAddress()->getPhoneNumber() ? $order->getShippingAddress()->getPhoneNumber() : "0600000000";
            $addresseeEmail = $order->getCustomer()->getEmail();
            $returnTypeChoice = "2"; // Return to the sender as priority parcel. "3" to not return to the sender
            $includeCustomsDeclarations = "1"; // include CN23 generation. "0" to not include it
            $categoryValue = "3"; // business shipment
            $invoiceNumber = "xxx00000"; // if invoice missing

            // build query

            $xml = new \SimpleXMLElement('<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" />');
            $xml->addChild('soapenv:Header');
            $children = $xml->addChild('soapenv:Body');
            $children = $children->addChild('sls:generateLabel', null, 'http://sls.ws.coliposte.fr');
            $children = $children->addChild('generateLabelRequest', null, '');
            $children->addChild('contractNumber', $transporter->getAccountNumber());
            $children->addChild('password', $transporter->getPassword());
            $outputFormat = $children->addChild('outputFormat');
            $outputFormat->addChild('outputPrintingType', $outputPrintingType);
            $letter = $children->addChild('letter');
            $service = $letter->addChild('service');
            $service->addChild('productCode', $serviceCode);
            $service->addChild('depositDate', $depositDate);
            $service->addChild('transportationAmount', (string)$shippingCosts);
            $service->addChild('totalAmount', (string)$order->getTotal());
            $service->addChild('orderNumber', $order->getNumber());
            $service->addChild('commercialName', "");
            $service->addChild('returnTypeChoice', $returnTypeChoice);
            $parcel = $letter->addChild('parcel');
            $parcel->addChild('weight', (string)$label->getTotalWeight());
            $customsDeclarations = $letter->addChild('customsDeclarations');
            $customsDeclarations->addChild('includeCustomsDeclarations', $includeCustomsDeclarations);
            $contents = $customsDeclarations->addChild('contents');

            /** @var LabelItem $labelItem */
            foreach ($label->getLabelItems() as $labelItem) { // article tag
                $itemDescription = $labelItem->getOrderItem()->getVariant()->getProduct()->getTranslation()->getDescription() ? $labelItem->getOrderItem()->getVariant()->getProduct()->getTranslation()->getDescription() : "description";
                $itemPrice = number_format($labelItem->getOrderItem()->getUnitPrice() / 100, 2, '.', ',');
                $hsCode = $labelItem->getOrderItem()->getProduct()->getHsCode() ? $labelItem->getOrderItem()->getProduct()->getHsCode() : "841391";

                $article = $contents->addChild('article');
                $article->addChild('description', $itemDescription ? $itemDescription : "description");
                $article->addChild('quantity', (string)$labelItem->getQuantity());
                $article->addChild('weight', (string)$labelItem->getWeight());
                $article->addChild('value', $itemPrice);
                $article->addChild('hsCode', $hsCode);
                $article->addChild('originCountry', $channel->getShopBillingData()->getCountryCode());
                $article->addChild('currency', $channel->getBaseCurrency()->getCode());
            }

            $category = $contents->addChild('category');
            $category->addChild('value', $categoryValue);
            $invoice = $customsDeclarations->addChild('invoiceNumber', $invoiceNumber);
            $sender = $letter->addChild('sender');
            $senderAddress = $sender->addChild('address');
            $senderAddress->addChild('companyName', $channel->getShopBillingData()->getCompany());
            $senderAddress->addChild('line2', $channel->getShopBillingData()->getStreet());
            $senderAddress->addChild('countryCode', $channel->getShopBillingData()->getCountryCode());
            $senderAddress->addChild('city', $channel->getShopBillingData()->getCity());
            $senderAddress->addChild('zipCode', $channel->getShopBillingData()->getPostcode());
            $senderAddress->addChild('phoneNumber', $phoneNumber);
            $senderAddress->addChild('email', $email);
            $addressee = $letter->addChild('addressee');
            $addresseeAddress = $addressee->addChild('address');
            $addresseeAddress->addChild('lastName', $params['letter']['addressee']['address']['lastName']);
            $addresseeAddress->addChild('firstName', $params['letter']['addressee']['address']['firstName']);
            $addresseeAddress->addChild('line2', $params['letter']['addressee']['address']['line2']);
            $addresseeAddress->addChild('countryCode', $params['letter']['addressee']['address']['countryCode']);
            $addresseeAddress->addChild('city', $params['letter']['addressee']['address']['city']);
            $addresseeAddress->addChild('zipCode', $params['letter']['addressee']['address']['zipCode']);
            $addresseeAddress->addChild('phoneNumber', $addresseePhoneNumber);
            $addresseeAddress->addChild('email', $addresseeEmail);
            if ($serviceCode === 'CDS') {
                $fields = $children->addChild('fields');
                $length = $fields->addChild('field');
                $length->addChild('key', 'LENGTH');
                $length->addChild('value', '50');
                $width = $fields->addChild('field');
                $width->addChild('key', 'WIDTH');
                $width->addChild('value', '50');
                $height = $fields->addChild('field');
                $height->addChild('key', 'HEIGHT');
                $height->addChild('value', '50');
            }
        }
        else {
            //+ Generate SOAPRequest
            $xml = new \SimpleXMLElement('<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" />');
            $xml->addChild('soapenv:Header');
            $children = $xml->addChild('soapenv:Body');
            $children = $children->addChild('sls:generateLabel', null, 'http://sls.ws.coliposte.fr');
            $children = $children->addChild('generateLabelRequest', null, '');
        }


        if ($serviceCode !== 'COM' && $serviceCode !== 'CDS') {
            $this->array_to_xml($params, $children);
        } else {
            $this->array_to_xml([], $children);
        }
        $requestSoap = $xml->asXML();

        //+ Call Web Service
        $resp = new \SoapClient('https://ws.colissimo.fr/sls-ws/SlsServiceWS/2.0?wsdl');
        $response['response'] = $resp->__doRequest($requestSoap, 'https://ws.colissimo.fr/sls-ws/SlsServiceWS', 'generateLabel', 2, false);
        if ($serviceCode !== 'COM' && $serviceCode !== 'CDS') {
            $response['params'] = $params;
        }
        return $response;
    }

    public function getPickupPoints(Order $order, float $weight, Transporter $transporter): array
    {
        $now = new \DateTime();
        $depositDate = $now->modify('+5 day')->format('d/m/Y');
        $address = $order->getShippingAddress();

        $soapClient = new \SoapClient('https://ws.colissimo.fr/pointretrait-ws-cxf/PointRetraitServiceWS/2.0?wsdl', [
            'trace' => 1,
            'soap_version' => \SOAP_1_1,
        ]);

        $params = [
            'accountNumber' => 'Arobases2022',
            'password' => 'aro@2022!Sylius',
            'address' => $address->getStreet(),
            'zipCode' => $address->getPostcode(),
            'city' => $address->getCity(),
            'countryCode' => $address->getCountryCode(),
            'weight' => $weight,
            'shippingDate' => $depositDate,
            'filterRelay' => '1',
            'requestId' => '1',
            'lang' => 'FR',
            'optionInter' => '1',
        ];
        $response = $soapClient->findRDVPointRetraitAcheminement($params);

        $responseArray = json_decode(json_encode($response), true);

        return $responseArray['return'];
    }

    private function array_to_xml($soapRequest, $soapRequestXml) {
        foreach($soapRequest as $key => $value) {
            if(is_array($value)) {
                if(!is_numeric($key)){
                    $subnode = $soapRequestXml->addChild("$key");
                    $this->array_to_xml($value, $subnode);
                }
                else{
                    $subnode = $soapRequestXml->addChild("item$key");
                    $this->array_to_xml($value, $subnode);
                }
            }
            else {
                $soapRequestXml->addChild("$key",htmlspecialchars("$value"));
            }
        }
    }
}
