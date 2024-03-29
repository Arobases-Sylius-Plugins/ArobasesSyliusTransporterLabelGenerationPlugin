<?php

namespace Arobases\SyliusTransporterLabelGenerationPlugin\Connector\Api\Colissimo;

use Sylius\Component\Core\Model\Channel;
use Arobases\SyliusTransporterLabelGenerationPlugin\Entity\Label;
use Arobases\SyliusTransporterLabelGenerationPlugin\Entity\Transporter;
use App\Entity\Order\Order;

interface ColissimoRequestInterface
{
    public function generateLabel(Channel $channel, Label $label, Transporter $transporter, string $outputPrintingType, string $depositDate): array;

    public function getPickupPoints(Order $order, float $weight, Transporter $transporter): array;
}
