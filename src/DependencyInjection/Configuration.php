<?php

declare(strict_types=1);

namespace Arobases\SyliusTransporterLabelGenerationPlugin\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    /**
     * @psalm-suppress UnusedVariable
     */
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('arobases_sylius_transporter_label_generation_plugin');
        $rootNode = $treeBuilder->getRootNode();

        return $treeBuilder;
    }
}
