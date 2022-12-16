<?php

declare(strict_types=1);

namespace Arobases\SyliusTransporterLabelGenerationPlugin\Connector\Api\Colissimo;

use Arobases\SyliusTransporterLabelGenerationPlugin\Entity\Label;
use Arobases\SyliusTransporterLabelGenerationPlugin\Entity\LabelItem;
use Arobases\SyliusTransporterLabelGenerationPlugin\Entity\Transporter;
use Sylius\Component\Core\Model\Channel;
use Sylius\Component\Core\Model\Order;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class ColissimoRequest extends AbstractController
{
    public function generateLabel(Channel $channel, Label $label, Transporter $transporter, string $outputPrintingType, string $depositDate): array
    {
        $order = $label->getRelatedOrder();
        $serviceCode = $order->getShipments()->last()->getMethod()?->getTransporterCode();
        if (!$serviceCode) {
            $serviceCode = 'DOM';
        }

        if ($serviceCode !== 'COM' && $serviceCode !== 'CDS') {
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
        }

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

        //+ Generate SOAPRequest
        $xml = new \SimpleXMLElement('<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" />');
        $xml->addChild('soapenv:Header');
        $children = $xml->addChild('soapenv:Body');
        $children = $children->addChild('sls:generateLabel', null, 'http://sls.ws.coliposte.fr');
        $children = $children->addChild('generateLabelRequest', null, '');
        if ($serviceCode !== 'COM' && $serviceCode !== 'CDS') {
            $this->array_to_xml($params, $children);
        } else {
            $this->array_to_xml([], $children);
        }

        if ($serviceCode === 'COM' || $serviceCode === 'CDS') {
            // Add Article to CN23 mandatory to Internationnal
            $XmlArray = new \SimpleXMLElement($xml->asXML());
            $children->addChild('contractNumber', $transporter->getAccountNumber());
            $children->addChild('password', $transporter->getPassword());
            $outputFormat = $children->addChild('outputFormat');
            $outputFormat->addChild('outputPrintingType', $outputPrintingType);
            $letter = $children->addChild('letter');
            $service = $letter->addChild('service');
            $service->addChild('productCode', $serviceCode);
            $service->addChild('depositDate', $depositDate);
            $service->addChild('transportationAmount', 1750);
            $service->addChild('orderNumber', $order->getNumber());
            $service->addChild('returnTypeChoice', 2);
            $parcel = $letter->addChild('parcel');
            $parcel->addChild('weight', $label->getTotalWeight());
            $customsDeclarations = $letter->addChild('customsDeclarations');
            $customsDeclarations->addChild('includeCustomsDeclarations', 1);
            $contents = $customsDeclarations->addChild('contents');
            /** @var LabelItem $item */
            foreach ($label->getLabelItems() as $item) {
                $article = $contents->addChild('article');
                if ($item->getOrderItem()->getVariant()->getProduct()->getTranslation()->getDescription()) {
                    $article->addChild('description', $item->getOrderItem()->getVariant()->getProduct()->getTranslation()->getDescription());
                }
                $article->addChild('quantity', $item->getQuantity());
                $article->addChild('weight', $item->getWeight());
                $article->addChild('value', $item->getOrderItem()->getVariant()->getChannelPricingForChannel($channel)?->getPrice());
                $article->addChild('hsCode', 841391);
                $article->addChild('originCountry', $channel->getShopBillingData()->getCountryCode());
                $article->addChild('currency', 'EUR');
            }
            $category = $contents->addChild('category');
            $category->addChild('value', '3');
            $sender = $letter->addChild('sender');
            $senderAddress = $sender->addChild('address');
            $senderAddress->addChild('companyName', $channel->getShopBillingData()->getCompany());
            $senderAddress->addChild('line2', $channel->getShopBillingData()->getStreet());
            $senderAddress->addChild('countryCode', $channel->getShopBillingData()->getCountryCode());
            $senderAddress->addChild('city', $channel->getShopBillingData()->getCity());
            $senderAddress->addChild('zipCode', $channel->getShopBillingData()->getPostcode());
            $addressee = $letter->addChild('addressee');
            $addresseeAddress = $addressee->addChild('address');
            $addresseeAddress->addChild('lastName', $order->getCustomer()->getLastName());
            $addresseeAddress->addChild('firstName', $order->getCustomer()->getFirstName());
            $addresseeAddress->addChild('line2', $order->getShippingAddress()->getStreet());
            $addresseeAddress->addChild('countryCode', 'GF');
            $addresseeAddress->addChild('city', 'Cayenne');
            $addresseeAddress->addChild('zipCode', '97300');
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
