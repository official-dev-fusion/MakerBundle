<?php

namespace DF\MakerBundle;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class ScrudConfiguration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('scrud_config');
            
        $rootNode
            ->children()    
            ->scalarNode('entity')
                ->isRequired()
            ->end()
            ->scalarNode('prefix_directory')
                ->isRequired()
            ->end()
            ->scalarNode('prefix_route')
                ->isRequired()
            ->end()
            ->booleanNode('voter')
                ->isRequired()
            ->end()
            ->arrayNode('search')
            ->isRequired()
            ->children()
                ->booleanNode('filter')
                    ->isRequired()
                ->end()
                ->booleanNode('pagination')
                    ->isRequired()
                ->end()
                ->booleanNode('multi_select')
                    ->isRequired()
                ->end()
            ->end()
            ->end()
            ->arrayNode('create')
            ->isRequired()
            ->children()
                ->booleanNode('activate')
                    ->isRequired()
                ->end()
            ->end()
            ->end()
            ->arrayNode('read')
            ->isRequired()
            ->children()
                ->booleanNode('activate')
                    ->isRequired()
                ->end()
            ->end()
            ->end()
            ->arrayNode('update')
            ->isRequired()
            ->children()    
                ->booleanNode('activate')
                    ->isRequired()
                ->end()
                ->booleanNode('multi_select')
                    ->isRequired()
                ->end()
            ->end()
            ->end()
            ->arrayNode('delete')
            ->isRequired()
            ->children()
                ->booleanNode('activate')
                    ->isRequired()
                ->end()
                ->booleanNode('multi_select')
                    ->isRequired()
                ->end()
            ->end()
            ->end()
        ->end()
        ->validate()
        ->always()
        ->then(function($values) {
            if ($values['update']['multi_select']) {
                if (!$values['search']['multi_select']) {
                    throw new \LogicException(
                        'Invalid value, scrud_config.update.multi_select must be false if scrud_config.multi_select is false.'
                    );
                }
            }
            if ($values['delete']['multi_select']) {
                if (!$values['search']['multi_select']) {
                    throw new \LogicException(
                        'Invalid value, scrud_config.delete.multi_select must be false if scrud_config.multi_select is false.'
                    );
                }
            }
            
            if ($values['update']['multi_select']) {
                if (!$values['update']['activate']) {
                    throw new \LogicException(
                        'Invalid value, scrud_config.update.multi_select must be false if scrud_config.update.activate is false.'
                    );
                }
            }
            if ($values['delete']['multi_select']) {
                if (!$values['delete']['activate']) {
                    throw new \LogicException(
                        'Invalid value, scrud_config.delete.multi_select must be false if scrud_config.delete.activate is false.'
                    );
                }
            }
        });    
        
        return $treeBuilder;
    }
}
