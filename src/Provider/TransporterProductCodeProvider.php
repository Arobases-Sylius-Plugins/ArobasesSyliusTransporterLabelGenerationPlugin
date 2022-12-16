<?php

declare(strict_types=1);

namespace Arobases\SyliusTransporterLabelGenerationPlugin\Provider;

use Arobases\SyliusTransporterLabelGenerationPlugin\Transporter\Colissimo\ProductCode;

class TransporterProductCodeProvider {

    public function getAllProductCodes() {
        $colissimoProductCodes = ProductCode::VALUES;

        //todo: create arrays of others transporter product codes and merge it to final array

        return array_merge($colissimoProductCodes);
    }
}