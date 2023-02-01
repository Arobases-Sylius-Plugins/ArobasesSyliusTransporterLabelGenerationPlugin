<?php

declare(strict_types=1);

namespace  Arobases\SyliusTransporterLabelGenerationPlugin\Twig\Extensions;

use Sylius\Component\Core\Model\Order;
use Sylius\Component\Core\Model\OrderItem;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class ProductExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('arobases_label_generation_has_all_hs_code', [$this, 'hasAllHsCode']),
        ];
    }

    public function hasAllHsCode(Order $order): bool
    {
        $allHsCodeValid = true;
        /** @var OrderItem $orderItem */
        foreach ($order->getItems() as $orderItem) {
            if (!$orderItem->getProduct()->getHsCode())
                $allHsCodeValid = false;
        }
        return $allHsCodeValid;
    }
}
