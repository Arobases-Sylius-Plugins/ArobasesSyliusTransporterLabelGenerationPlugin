<?php

namespace Arobases\SyliusTransporterLabelGenerationPlugin\Connector\Api\Chronopost;

use Sylius\Component\Core\Model\Channel;
use Arobases\SyliusTransporterLabelGenerationPlugin\Entity\Label;
use Arobases\SyliusTransporterLabelGenerationPlugin\Entity\Transporter;
use App\Entity\Order\Order;

interface ChronopostRequestInterface
{
    public function generateLabel();
//    public function generateLabel(Channel $channel, Label $label, Transporter $transporter, string $outputPrintingType, string $depositDate): array;

//    public function getPickupPoints(Order $order, float $weight, Transporter $transporter): array;
}
