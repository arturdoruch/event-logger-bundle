<?php

namespace ArturDoruch\EventLoggerBundle\DependencyInjection;

use ArturDoruch\EventLoggerBundle\Log;
use ArturDoruch\Tool\ClassValidator;
use Psr\Log\LogLevel;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * @author Artur Doruch <arturdoruch@interia.pl>
 */
class Configuration implements ConfigurationInterface
{
    private $name = 'artur_doruch_event_logger';


    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root($this->name);

        $rootNode
            ->children()
                ->arrayNode('event_processor_services')
                    ->beforeNormalization()
                    ->ifString()->then(function ($v) {
                        return [$v];
                    })
                    ->end()
                    ->prototype('scalar')->end()
                ->end()
                ->append($this->createLogNode())
                ->append($this->createLogViewingNode())
            ->end();

        return $treeBuilder;
    }


    private function createLogNode()
    {
        $builder = new TreeBuilder();
        $node = $builder->root('log');

        $node
            ->beforeNormalization()
            ->always(function ($v) {
                if (empty($v['entity_class']) &&
                    (isset($v['driver_service']) && $v['driver_service'] === 'arturdoruch_eventlogger.database_log_driver'
                    || isset($v['handler_services']) && in_array('arturdoruch_eventlogger.database_log_handler', $v['handler_services']))
                ) {
                    throw new InvalidConfigurationException(sprintf(
                        'The key "entity_class" at path "%s.log" must be configured' .
                        ' (Required by configured "arturdoruch_eventlogger.database_log_driver"' .
                        ' and "arturdoruch_eventlogger.database_log_handler" services).',
                        $this->name
                    ));
                }

                return $v;
            })
            ->end()
            ->children()
                ->scalarNode('class')
                    ->cannotBeEmpty()->defaultValue(Log::class)
                    ->beforeNormalization()
                        ->always(function ($v) {
                            if ($v) {
                                ClassValidator::validateSubclassOf($v, Log::class, 'log');
                            }

                            return $v;
                        })
                    ->end()
                ->end()
                ->scalarNode('entity_class')->cannotBeEmpty()
                ->end()
                ->arrayNode('handler_services')
                    ->beforeNormalization()
                        ->ifString()->then(function ($v) {
                            return [$v];
                        })
                    ->end()
                    ->scalarPrototype()->end()
                    ->requiresAtLeastOneElement()
                ->end()
            ->end();

        return $node;
    }


    private function createLogViewingNode()
    {
        $builder = new TreeBuilder();
        $node = $builder->root('log_viewing');

        $node
            ->children()
                ->scalarNode('base_template')
                    ->isRequired()->cannotBeEmpty()
                    ->info('Twig base template.')
                ->end()
                ->scalarNode('context_template')
                    ->defaultValue('@ArturDoruchEventLogger/log/context.html.twig')->cannotBeEmpty()
                    ->info('The log context template.')
                ->end()
                ->scalarNode('driver_service')
                    ->info('Service name of the log driver to use by LogController to view and manage logs.')
                    ->isRequired()
                    ->cannotBeEmpty()
                ->end()
                ->arrayNode('context')
                    ->children()
                        ->arrayNode('object_converters')
                            ->info('The class name of the log context object converter.')
                            ->scalarPrototype()->end()
                        ->end()
                        ->arrayNode('value_formatters')
                            ->arrayPrototype()
                                ->beforeNormalization()
                                    ->always(function ($v) {
                                        if (is_string($v)) {
                                            $v = ['class' => $v];
                                        }

                                        return $v;
                                    })
                                ->end()
                                ->children()
                                    ->scalarNode('class')
                                        ->isRequired()->cannotBeEmpty()
                                        ->info('The class name of the log context value formatter.')
                                    ->end()
                                    ->scalarNode('template')->cannotBeEmpty()->end()
                                    ->arrayNode('options')
                                        ->scalarPrototype()->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('filter_form')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('choice_placeholder')->defaultValue('-- all --')->end()
                        ->arrayNode('category_choices')
                            ->beforeNormalization()
                            ->always(function ($v) {
                                if (is_string($v) && is_callable($v)) {
                                    $v = call_user_func($v);
                                }

                                return $v;
                            })
                            ->end()
                            ->scalarPrototype()->end()
                        ->end()
                        ->arrayNode('level_choices')
                            ->beforeNormalization()
                            ->always(function ($v) {
                                if (is_string($v) && is_callable($v)) {
                                    $v = call_user_func($v);
                                }

                                return $v;
                            })
                            ->end()
                            ->scalarPrototype()->end()
                            ->defaultValue([
                                LogLevel::CRITICAL,
                                LogLevel::ERROR,
                                LogLevel::WARNING,
                                LogLevel::NOTICE,
                                LogLevel::INFO,
                                'error_and_higher',
                                'warning_and_lower',
                            ])
                            ->end()
                        ->end()
                    ->end()
                ->arrayNode('list_item_limits')
                    ->integerPrototype()->end()
                    ->defaultValue([50, 100, 150, 200, 300, 500])
                ->end()
            ->end();

        return $node;
    }
}
