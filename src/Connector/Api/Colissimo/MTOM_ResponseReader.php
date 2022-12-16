<?php

declare(strict_types=1);

namespace Arobases\SyliusTransporterLabelGenerationPlugin\Connector\Api\Colissimo;

class MTOM_ResponseReader
{
    private $CONTENT_TYPE;

    private $UUID;

    private $CONTENT;

    public $attachments = [];

    public $soapResponse = [];

    public $uuid;

    public function __construct($response)
    {
        $this->CONTENT_TYPE = 'Content-Type: application/xop+xml;';
        $this->UUID = '/--uuid:/'; //This is the separator of each part of the response
        $this->CONTENT = 'Content-';
        $this->parseResponse($response);
    }

    private function parseResponse($response)
    {
        $content = [];
        $matches = [];
        preg_match_all($this->UUID, $response, $matches, \PREG_OFFSET_CAPTURE);
        for ($i = 0; $i < count($matches[0]) - 1; ++$i) {
            if ($i + 1 < count($matches[0])) {
                $content[$i] = substr($response, $matches[0][$i][1], $matches[0][$i + 1][1] - $matches[0][$i][1]);
            } else {
                $content[$i] = substr($response, $matches[0][$i][1], strlen($response));
            }
        }
        foreach ($content as $part) {
            if ($this->uuid == null) {
                $uuidStart = 0;
                $uuidEnd = 0;

                $this->uuid = substr($part, $uuidStart, $uuidEnd - $uuidStart);
            }
            $header = $this->extractHeader($part);
            if (count($header) > 0) {
                if (strpos($header['Content-Type'], 'type="text/xml"') !== false) {
                    $this->soapResponse['header'] = $header;
                    $this->soapResponse['data'] = trim(substr($part, $header['offsetEnd']));
                } else {
                    $attachment['header'] = $header;
                    $attachment['data'] = trim(substr($part, $header['offsetEnd']));
                    array_push($this->attachments, $attachment);
                }
            }
        }
    }

    /**
     * Exclude the header from the Web Service response
     *
     * @param string $part
     *
     * @return array $header
     */
    private function extractHeader($part)
    {
        $header = [];
        $headerLineStart = strpos($part, $this->CONTENT, 0);
        $endLine = 0;
        while ($headerLineStart !== false) {
            $header['offsetStart'] = $headerLineStart;
            $endLine = strpos($part, "\r\n", $headerLineStart);
            $headerLine = explode(': ', substr($part, $headerLineStart, $endLine - $headerLineStart));
            $header[$headerLine[0]] = $headerLine[1];
            $headerLineStart = strpos($part, $this->CONTENT, $endLine);
        }
        $header['offsetEnd'] = $endLine;

        return $header;
    }

    private function arrayToXml($soapRequest, $soapRequestXml)
    {
        foreach ($soapRequest as $key => $value) {
            if (is_array($value)) {
                if (!is_numeric($key)) {
                    $subnode = $soapRequestXml->addChild("$key");
                    $this->array_to_xml($value, $subnode);
                } else {
                    $subnode = $soapRequestXml->addChild("item$key");
                    $this->array_to_xml($value, $subnode);
                }
            } else {
                $soapRequestXml->addChild("$key", htmlspecialchars("$value"));
            }
        }
    }
}
