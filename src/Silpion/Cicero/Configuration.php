<?php

namespace Silpion\Cicero;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\Processor;


class Configuration
{
    public function process(array $values)
    {
        $processor = new Processor();

        return $processor->process($this->getTree(), array($values));
    }

    /**
     * @return ArrayNodeDefinition
     * @throws \Exception
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
                        ->scalarNode('extension')
                            ->defaultValue('php')
                        ->end()
                        ->arrayNode('paths')
                            ->prototype('scalar')->end()
                            ->defaultValue(array('.'))
                        ->end()
                        ->arrayNode('excluded_paths')
                            ->prototype('scalar')->end()
                            ->defaultValue(array('vendor/'))
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $tb->buildTree();
    }
}