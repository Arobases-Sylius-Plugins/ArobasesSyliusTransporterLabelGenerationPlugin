<?php

namespace Arobases\SyliusTransporterLabelGenerationPlugin\Connector\Api\Chronopost;

use Sylius\Component\Core\Model\Channel;
use Arobases\SyliusTransporterLabelGenerationPlugin\Entity\Label;
use Arobases\SyliusTransporterLabelGenerationPlugin\Entity\Transporter;

interface ChronopostRequestInterface
{
    public function generateLabel(Channel $channel, Label $label, Transporter $transporter, string $outputPrintingType, string $depositDate): array;

}
