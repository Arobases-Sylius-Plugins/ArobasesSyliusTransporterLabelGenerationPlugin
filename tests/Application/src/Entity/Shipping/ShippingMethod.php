<?php

declare(strict_types=1);

namespace Tests\Arobases\SyliusTransporterLabelGenerationPlugin\Entity\Shipping;

use Arobases\SyliusTransporterLabelGenerationPlugin\Entity\TransporterTrait;
use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Core\Model\ShippingMethod as BaseShippingMethod;

/**
 * @ORM\Entity
 * @ORM\Table(name="sylius_shipping_method")
 */
class ShippingMethod extends BaseShippingMethod {
    use TransporterTrait;
}