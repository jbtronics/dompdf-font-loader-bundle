<?php

namespace Jbtronics\DompdfFontLoaderBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{

    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('dompdf_font_loader');
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->children()

            ->append($this->getAutodiscoveryNode())

            ->booleanNode('auto_install')->defaultFalse()->end()

            ->arrayNode('fonts')
                ->arrayPrototype()
                ->children()
                    ->scalarNode('normal')->isRequired()->cannotBeEmpty()->end()
                    ->scalarNode('bold')->end()
                    ->scalarNode('italic')->end()
                    ->scalarNode('bold_italic')->end()
                ->end()
            ->beforeNormalization()->castToArray()->end()
            ->end()


         ->end();

        return $treeBuilder;
    }

    private function getAutodiscoveryNode(): ArrayNodeDefinition
    {
        $node = new ArrayNodeDefinition('autodiscovery');
        $node
            ->children()
            ->arrayNode('paths')
                ->scalarPrototype()->end()->defaultValue([])
                ->beforeNormalization()->castToArray()->end()
            ->end()
            ->arrayNode('exclude_patterns')
                ->scalarPrototype()->end()->defaultValue([])
                ->beforeNormalization()->castToArray()->end()
            ->end()
                ->scalarNode('file_pattern')->defaultValue('/\.(ttf)$/')->end()
            ->end()
            ->end()
            ;

            $node->canBeDisabled();

        return $node;

    }

}