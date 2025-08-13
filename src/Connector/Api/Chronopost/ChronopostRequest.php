<?php

declare(strict_types=1);

namespace Arobases\SyliusTransporterLabelGenerationPlugin\Connector\Api\Chronopost;

use Arobases\SyliusTransporterLabelGenerationPlugin\Entity\Label;
use Arobases\SyliusTransporterLabelGenerationPlugin\Entity\LabelItem;
use Arobases\SyliusTransporterLabelGenerationPlugin\Entity\Transporter;
use Arobases\SyliusTransporterLabelGenerationPlugin\Repository\TransporterRepository;
use Sylius\Component\Core\Model\Adjustment;
use Sylius\Component\Core\Model\Channel;
use Sylius\Component\Core\Model\Order;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ChronopostRequest extends AbstractController implements ChronopostRequestInterface
{
    const WSDL_SHIPPING_SERVICE = "https://ws.chronopost.fr/shipping-cxf/ShippingServiceWS?wsdl";
    private string $apiUrl = 'https://www.chronopost.fr/chronopost-pickup-point-search-rest-api/rest/searchPoints';

    public function generateLabel()
    {
        $shipping_params = [
            // Chronopost account api password / Mot de passe Api Chronopost
            'password'                  => '666666',
            // Chronopost account number / numéro compte client chronopost
            'headerValue'                   => [
                "accountNumber"             => '666666',
                "subAccount"                => '',
                "idEmit"                    => 'FR',
                "identWebPro"               => '',
            ],
            // Shipper / Expediteur
            'shipperValue' => [
                "shipperAdress1"            => '1 rue du Général',
                "shipperAdress2"            => '',
                "shipperCity"               => 'RODEZ',
                "shipperCivility"           => 'M',
                "shipperContactName"        => 'George TENANT',
                "shipperCountry"            => 'FR',
                "shipperCountryName"        => 'FRANCE',
                "shipperEmail"              => 'george.tenant@classe.com',
                "shipperMobilePhone"        => '0611223344',
                "shipperName"               => 'George TENANT',
                "shipperName2"              => '',
                "shipperPhone"              => '0611223344',
                // @var intType de préalerte (MAS) -> 0 : pas de préalerte | 11 : abonnement tracking expéditeur
                "shipperPreAlert"           => 0,
                "shipperZipCode"            => '12000',
            ],
            // Customer / Client
            'customerValue' => [
                "customerAdress1"           => '40 RUE J. JAURES',
                "customerAdress2"           => 'res 2 etage 3 porte 8',
                "customerCity"              => 'BIARRITZ',
                "customerCivility"          => 'M',
                "customerContactName"       => 'Jeanne-Coralie BARTA',
                "customerCountry"           => 'FR',
                "customerCountryName"       => 'FRANCE',
                "customerEmail"             => 'jc@coralie.com',
                "customerMobilePhone"       => '0624278556',
                "customerName"              => 'Jeanne-Coralie BARTA',
                "customerName2"             => '',
                "customerPhone"             => '0624278556',
                "customerPreAlert"          => 0,
                "customerZipCode"           => '64200',
                // Utiliser comme expediteur sur l'etiquette finale O/N
                "printAsSender"             => 'N',
            ],
            // Recipient / Destinataire
            'recipientValue' => [
                "recipientAdress1"          => '40 RUE JEAN PASCOU',
                "recipientAdress2"          => '',
                "recipientCity"             => 'BIGANOS',
                "recipientContactName"      => 'Joe Doe',
                "recipientCountry"          => 'FR',
                "recipientCountryName"      => 'FRANCE',
                "recipientEmail"            => 'jdoe@doremi.fr',
                "recipientMobilePhone"      => '0644444444',
                "recipientName"             => '',
                "recipientName2"            => '',
                "recipientPhone"            => '',
                "recipientPreAlert"         => 0,
                "recipientZipCode"          => '33160',
                "recipientCivility"         => 'M',
            ],
            // Sky Bill / Etiquette de livraison / Caractéristique du colis
            'skybillValue' => [
                // Code Produit Chronopost [0 : Chrono Retrait Bureau | 1 : Chrono 13 | 86 : Chrono Relais | cf Docts ANNEXE 8 ]
                "productCode"               => '86',
                // Unité poids | defaut: KGM (Kilogrammes) | recommandation 20 de l’UN/ECE
                "weightUnit"                => 'KGM',
                "shipDate"                  => date('c'),       // Date d'expédition (dateTime)
                "shipHour"                  => date('G'),       // Heure d'expédition - Heure de génération de l'envoi (heure  courante), entre 0 et 23 - (int)
                "weight"                    => 0.4,               // Poids en KG (float)
                // Jour de livraison : 0 - Normal | 1 - Livraison lundi (FR) | 6 - Livraison samedi (FR) (string)
                "service"                   => '0',
                // Type colis (DOC:documents/MAR:marchandises) (string)
                "objectType"                => 'MAR',
                "bulkNumber"                => 1,               // Nombre total de colis
                "codCurrency"               => 'EUR',           // Devise du Retour Express de paiement EUR (Euro) par defaut
                "codValue"                  => 0,               // Valeur Retour Express paiement
                "customsCurrency"           => 'EUR',           // Devise valeur déclarée en douane (string)
                "customsValue"              => 0,               // Valeur déclarée en douane (int)
                "evtCode"                   => 'DC',            // Code événement suivi Chronopost - Champ fixe : DC (string)
                "insuredCurrency"           => 'EUR',           // Devise valeur assurée (string)
                "insuredValue"              => 0,               // Valeur assurée (int)
                "masterSkybillNumber"       => '?',
                "portCurrency"              => 'EUR',           // string
                "portValue"                 => 0,               // float
                "skybillRank"               => 1,               // string  ?????
                "height"                    => '10',
                "length"                    => '20',
                "width"                     => '30',
            ],
            // Pickup On Request parameters / Paramètres Enlèvement Sur Demande
            'esdValue' => [
                "closingDateTime"           => '',              // dateTime
                "height"                    => '',              // float
                "length"                    => '',              // float
                "retrievalDateTime"         => '',              // dateTime
                "shipperBuildingFloor"      => '',              // string
                "shipperCarriesCode"        => '',              // string
                "shipperServiceDirection"   => '',              // string
                "specificInstructions"      => '',              // string
                "width"                     => '',              // float
            ],
            // Reference values / Valeurs de réference
            'refValue' => [
                "customerSkybillNumber"     => '',              // string Numéro colis client 15 carac max -> code barre A4 - ex 123456789
                "PCardTransactionNumber"    => '',              // string
                "recipientRef"              => '',              // string Référence Destinataire - Champ libre (imprimable sur la facture) - critère de recherche suivi (ex: '24') (*)
                "shipperRef"                => '',              // string Référence Expéditeur - Champ libre (imprimable sur la facture) - critère de recherche suivi ->
                // * Chrono Relais (86), Chrono Relais 9 (80), Chrono Relais Europe (3T)*  et Chrono Zengo Relais 13 (3K)
                // remplir avec code du point relais Réf Expéditeur (ex: '000000000000001')
            ],
            // Skybill Params Value / Etiquette de livraison - format de fichiers /datas
            'skybillParamsValue' => [
                "mode"           => 'PDF',                          // Format final etiquette : default PDF | ...
            ],
        ];

        if(false !== $this->soapCheck()) {
            $chrono_id = uniqId();
            try {
                $result = $this->soapLaunch($shipping_params);
//                $result = $client->shippingMultiParcelV5($params);
//                $label = base64_decode($result->resultParcelValue[0]->pdfEtiquette);
//                file_put_contents('etiquette.pdf', $label);
            } catch (\SoapFault $soapFault) {
                dump($soapFault);
                exit($soapFault->faultstring);
            }
            if ($result->return->errorCode) {
                echo 'Erreur n° ' . $result->return->errorCode . ' : ' .
                    $result->return->errorMessage;
                dump('echec');
                dump($result);
            } else {
                dump('success');
                dump($result);
//                $fp = fopen('pdf/chronopost_'.trim($chrono_id).'.pdf', 'w');
//                fwrite($fp, $result->return->skybill);
//                fclose($fp);
//                echo 'MaBoutique.fr -> récuperer mon etiquette en PDF : <a href="/pdf/chronopost_'.trim($chrono_id).'.pdf">chronopost '.trim($chrono_id).'</a><br>' . PHP_EOL;
            }
        } else {
            dump('Soap not installed');
        }
    }

    public function getPickupPoints(string $zipcode, string $city = '', int $maxPoints = 10): array
    {
        $params = [
            'address' => $zipcode,
            'zipCode' => $zipcode,
            'city' => $city,
            'countryCode' => 'FR',
            'type' => 'P', // P = Pickup point
            'maxPointChronopost' => $maxPoints,
            'maxDistanceSearch' => 20, // km
        ];

        $url = $this->apiUrl . '?' . http_build_query($params);

        $context = stream_context_create([
            'http' => [
                'method' => 'GET',
                'header' => "Accept: application/json\r\n"
            ]
        ]);

        $result = file_get_contents($url, false, $context);

        if ($result === false) {
            throw new \RuntimeException("Erreur lors de la récupération des points relais Chronopost.");
        }

        $data = json_decode($result, true);

        if (!is_array($data)) {
            throw new \RuntimeException("Réponse JSON invalide de Chronopost.");
        }

        return $data;

        // exemple d'utilisation
//        $client = new ChronopostPickupClient();
//        $points = $client->getPickupPoints('44000', 'Nantes', 5);
//
//        foreach ($points as $point) {
//            echo "{$point['ident']} - {$point['nom']} - {$point['adresse1']} {$point['codePostal']} {$point['localite']}\n";
//        }
    }

    /**
     * Check Soap PHP extension availability
     */
    public function soapCheck() : bool
    {
        if (!extension_loaded('soap')) {
            return false;
        }
        return true;
    }
    /**
     * Launch the Soap client with Chronopost wsdl and parameters
     */
    public function soapLaunch(array $params)
    {

        $chronopost_client = new \soapClient(self::WSDL_SHIPPING_SERVICE);
        $chronopost_client->soap_defencoding = 'UTF-8';
        $chronopost_client->decode_utf8 = false;

        return $chronopost_client->shipping($params);
    }
}
