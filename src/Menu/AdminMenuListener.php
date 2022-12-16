<?php

declare(strict_types=1);

namespace Arobases\SyliusTransporterLabelGenerationPlugin\Menu;

use Sylius\Bundle\UiBundle\Menu\Event\MenuBuilderEvent;

final class AdminMenuListener
{
    public function addAdminMenuItems(MenuBuilderEvent $event): void
    {
        $menu = $event->getMenu();
        $menu->getChild('sales')->addChild('labelGeneration', [
            'route' => 'arobases_sylius_transporter_label_generation_plugin_admin_transporter_index',
        ])->setLabel('arobases_sylius_transporter_label_generation_plugin.menu.admin.label_generation')->setLabelAttribute('icon', 'tag');
    }
}
