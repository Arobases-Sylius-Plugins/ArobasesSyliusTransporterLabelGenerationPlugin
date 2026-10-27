<?php

declare(strict_types=1);

namespace Arobases\SyliusTransporterLabelGenerationPlugin\Connector\Api\Chronopost;

use Arobases\SyliusTransporterLabelGenerationPlugin\Entity\Label;
use Arobases\SyliusTransporterLabelGenerationPlugin\Entity\Transporter;
use Sylius\Component\Core\Model\Channel;
use Sylius\Component\Core\Model\Order;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Intl\Countries;

final class ChronopostRequest extends AbstractController implements ChronopostRequestInterface
{
    const WSDL_SHIPPING_SERVICE = "https://ws.chronopost.fr/shipping-cxf/ShippingServiceWS?wsdl";
    private string $pickupApiUrl = 'https://www.chronopost.fr/chronopost-pickup-point-search-rest-api/rest/searchPoints';


    /**
     * Génère l'enveloppe SOAP et appelle Chronopost
     */
    public function generateLabel(
        Channel $channel,
        Label $label,
        Transporter $transporter,
        string $outputPrintingType = 'PDF',
        string $depositDate = null
    ): array {
        $order = $label->getRelatedOrder();
        $billingAddress = $order->getBillingAddress();
        $shippingAddress = $order->getShippingAddress();
        $serviceCode = $order->getShipments()->last()?->getTransporterCode();

        $customer = $order->getCustomer();
        $now = new \DateTime();

        // Si aucune date de dépôt passée, utiliser aujourd'hui
        if ($depositDate === null) {
            $depositDate = $now->format('Y-m-d');
        }

        // Construire l’enveloppe SOAP
        $xml = new \SimpleXMLElement('<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:cxf="http://cxf.shipping.soap.chronopost.fr/"/>');
        $xml->addChild('soapenv:Header');
        $body = $xml->addChild('soapenv:Body');
        $shipping = $body->addChild('cxf:shippingMultiParcelV4');

        // Header Chronopost
        $headerValue = $shipping->addChild('headerValue');
        $headerValue->addChild('accountNumber', $transporter->getAccountNumber());
        $headerValue->addChild('idEmit', 'CHRFR');
        $headerValue->addChild('identWebPro', '');
        $headerValue->addChild('subAccount', '');

        $refValue = $shipping->addChild('refValue');
        $refValue->addChild('customerSkybillNumber', '');
        $refValue->addChild('recipientRef', $order->getNumber());
        $refValue->addChild('shipperRef', $label->getId());
        $refValue->addChild('idRelais', '');

        // Shipper
        $shipperValue = $shipping->addChild('shipperValue');
        $shipperValue->addChild('shipperAdress1', $channel->getShopBillingData()->getStreet());
        $shipperValue->addChild('shipperAdress2', '');
        $shipperValue->addChild('shipperCity', $channel->getShopBillingData()->getCity());
        $shipperValue->addChild('shipperCivility', 'M');
        $shipperValue->addChild('shipperContactName', $channel->getShopBillingData()->getCompany() ?? 'NOM CONTACT');
        $shipperValue->addChild('shipperCountry', $channel->getShopBillingData()->getCountryCode());
        $shipperValue->addChild('shipperCountryName', 'FRANCE');
        $shipperValue->addChild('shipperEmail', $channel->getContactEmail() ?? 'mail@mail.fr');
        $shipperValue->addChild('shipperMobilePhone', '');
        $shipperValue->addChild('shipperName', $channel->getShopBillingData()->getCompany());
        $shipperValue->addChild('shipperName2', '');
        $shipperValue->addChild('shipperPhone', $channel->getContactPhoneNumber() ?? '0102030405');
        $shipperValue->addChild('shipperPreAlert', 0);
        $shipperValue->addChild('shipperZipCode', $channel->getShopBillingData()->getPostcode());
        $shipperValue->addChild('shipperType', 1);

        // Customer
        $countryCode = $order->getShippingAddress()->getCountryCode(); // ex: 'FR'
        $locale = $order->getLocaleCode();
        $countryName = Countries::getName($countryCode, $locale);
        $customerValue = $shipping->addChild('customerValue');
        $customerValue->addChild('customerAdress1', $billingAddress->getStreet());
        $customerValue->addChild('customerAdress2', '');
        $customerValue->addChild('customerCity', $billingAddress->getCity());
        $customerValue->addChild('customerCivility', $billingAddress->getCustomer()->getGender());
        $customerValue->addChild('customerContactName', $customer->getFullName());
        $customerValue->addChild('customerCountry', $billingAddress->getCountryCode());
        $customerValue->addChild('customerCountryName', $countryName);
        $customerValue->addChild('customerEmail', $customer->getEmail());
        $customerValue->addChild('customerMobilePhone', '');
        $customerValue->addChild('customerName', $customer->getFullName());
        $customerValue->addChild('customerName2', '');
        $customerValue->addChild('customerPhone', $billingAddress->getPhoneNumber() ?? '0600000000');
        $customerValue->addChild('customerPreAlert', 0);
        $customerValue->addChild('customerZipCode', $billingAddress->getPostcode());
        $customerValue->addChild('printAsSender', 'N');

        // Recipient
        $recipientValue = $shipping->addChild('recipientValue');
        $recipientValue->addChild('recipientName', $shippingAddress->getFirstName());
        $recipientValue->addChild('recipientName2', $shippingAddress->getLastName());
        $recipientValue->addChild('recipientAdress1', $shippingAddress->getStreet());
        $recipientValue->addChild('recipientAdress2', '');
        $recipientValue->addChild('recipientZipCode', $shippingAddress->getPostcode());
        $recipientValue->addChild('recipientCity', $shippingAddress->getCity());
        $recipientValue->addChild('recipientCountry', $shippingAddress->getCountryCode());
        $recipientValue->addChild('recipientContactName', $shippingAddress->getFullName());
        $recipientValue->addChild('recipientEmail', $customer->getEmail());
        $recipientValue->addChild('recipientPhone', $shippingAddress->getPhoneNumber() ?? '0600000000');
        $recipientValue->addChild('recipientMobilePhone', $shippingAddress->getPhoneNumber() ?? '0600000000');
        $recipientValue->addChild('recipientPreAlert', 1);
        $recipientValue->addChild('recipientType', 2);



        // Skybill
        $skybillValue = $shipping->addChild('skybillValue');
        $skybillValue->addChild('shipDate', $depositDate);
        $skybillValue->addChild('shipHour', (int)$now->format('G'));
        $skybillValue->addChild('weight', $label->getTotalWeight());
        $skybillValue->addChild('weightUnit', 'KGM');
        $skybillValue->addChild('productCode', $serviceCode); // code Chronopost selon service

        // Paramètres d’impression
        $skybillParamsValue = $shipping->addChild('skybillParamsValue');
        $skybillParamsValue->addChild('mode', $outputPrintingType);
        $skybillParamsValue->addChild('duplicata', 'N');
        $skybillParamsValue->addChild('withReservation', '2');

        // Ajoute les autres champs requis (multiParcel, version, etc.)
        $shipping->addChild('password', $transporter->getPassword());
        $shipping->addChild('modeRetour', 2);
        $shipping->addChild('numberOfParcel', 1);
        $shipping->addChild('version', '2.0');
        $shipping->addChild('multiParcel', 'N');

        $requestSoap = $xml->asXML();

        // Appel du Web Service
        $resp = new \SoapClient(self::WSDL_SHIPPING_SERVICE);
        $response = $resp->__doRequest($requestSoap, self::WSDL_SHIPPING_SERVICE, 'shippingMultiParcelV4', 1);

        return [
            'response' => $response,
        ];
    }

    private function soapCheck(): bool
    {
        return extension_loaded('soap');
    }

    /**
     * Récupère les points relais Chronopost dynamiquement
     */
    public function getPickupPoints(string $zipcode, string $city = '', int $maxPoints = 10): array
    {
        $params = [
            'address' => $zipcode,
            'zipCode' => $zipcode,
            'city' => $city,
            'countryCode' => 'FR',
            'type' => 'P', // Pickup point
            'maxPointChronopost' => $maxPoints,
            'maxDistanceSearch' => 20,
        ];

        $url = $this->pickupApiUrl . '?' . http_build_query($params);

        $context = stream_context_create([
            'http' => [
                'method' => 'GET',
                'header' => "Accept: application/json\r\n",
            ],
        ]);

        $result = file_get_contents($url, false, $context);

        if ($result === false) {
            throw new \RuntimeException('Erreur lors de la récupération des points relais Chronopost.');
        }

        $data = json_decode($result, true);

        if (!is_array($data)) {
            throw new \RuntimeException('Réponse JSON invalide de Chronopost.');
        }

        return $data;
    }

}