<?php

namespace Silpion\Cicero;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\Processor;

/**
 * Configuration definition for the ".cicero.yml" file.
 *
 * @author Julius Beckmann <beckmann@silpion.de>
 */
class Configuration
{
    /**
     * Processes given values by the configuration definition.
     *
     * @param array $values
     * @return array
     */
    public function process(array $values)
    {
        $processor = new Processor();

        return $processor->process($this->getTree(), array($values));
    }

    /**
     * Returns the configuration tree.
     *
     * @return \Symfony\Component\Config\Definition\NodeInterface
     */
    public function getTree()
    {
        $tb = new TreeBuilder();

        $rootNode = $tb->root('{root}', 'array', new NodeBuilder());
        $rootNode
            ->children()
                // Configuration for composer.
                ->arrayNode('composer')
                    ->children()
                        ->booleanNode('enabled')
                            ->defaultFalse()
                        ->end()
                    ->end()
                ->end()
                // Configuration for PHP code.
                ->arrayNode('php')
                    ->children()
                        ->booleanNode('enabled')
                            ->defaultFalse()
                        ->end()
                        ->arrayNode('paths')
                            ->prototype('scalar')->end()
                            ->defaultValue(array('.')) // Default is to scan the whole project path '.'
                        ->end()
                        ->arrayNode('excluded_paths')
                            ->prototype('scalar')->end()
                            ->defaultValue(array('vendor/')) // Assume that composer default folder should be excluded.
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $tb->buildTree();
    }
}