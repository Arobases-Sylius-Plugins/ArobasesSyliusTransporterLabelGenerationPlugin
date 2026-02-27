<?php

declare(strict_types=1);

namespace Arobases\SyliusTransporterLabelGenerationPlugin\Provider;

use Arobases\SyliusTransporterLabelGenerationPlugin\Transporter\ProductCode;

class TransporterProductCodeProvider
{
    public function getAllProductCodes()
    {
        $productCodes = ProductCode::VALUES;


        return array_merge($productCodes);
    }
}
