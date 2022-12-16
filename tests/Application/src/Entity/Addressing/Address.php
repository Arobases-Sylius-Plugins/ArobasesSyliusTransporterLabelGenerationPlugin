<?php

declare(strict_types=1);

namespace Tests\Arobases\SyliusTransporterLabelGenerationPlugin\Entity\Addressing;

use Arobases\SyliusTransporterLabelGenerationPlugin\Entity\PickupPointTrait;
use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Core\Model\Address as BaseAddress;

/**
 * @ORM\Entity
 * @ORM\Table(name="sylius_address")
 */
class Address extends BaseAddress
{
    use PickupPointTrait;
}
