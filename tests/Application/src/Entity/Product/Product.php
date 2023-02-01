<?php

declare(strict_types=1);

namespace Tests\Arobases\SyliusTransporterLabelGenerationPlugin\Entity\Product;

use Arobases\SyliusTransporterLabelGenerationPlugin\Entity\HsCodeTrait;
use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Core\Model\Product as BaseProduct;

/**
 * @ORM\Entity
 * @ORM\Table(name="sylius_product")
 */
class Product extends BaseProduct {
    use HsCodeTrait;
}