<?php

namespace ArturDoruch\EventLoggerBundle\DependencyInjection;

use ArturDoruch\ClassValidator\ClassValidator;
use ArturDoruch\EventLoggerBundle\Log;
use Psr\Log\LogLevel;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
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
        $node = $this->createRootNode($this->name, $treeBuilder);
        $node
            ->beforeNormalization()
            ->always(function ($v) {
                $log = $v['log'] ?? [];

                if (!array_key_exists('entity_class', $log)) {
                    if (($v['log_viewing']['driver_service'] ?? null) === 'arturdoruch_eventlogger.database_log_driver') {
                        $configDetails = ['arturdoruch_eventlogger.database_log_driver', 'log_viewing.driver_service'];
                    } elseif (in_array('arturdoruch_eventlogger.database_log_handler', $log['handler_services'] ?? [])) {
                        $configDetails = ['arturdoruch_eventlogger.database_log_handler', 'log.handler_services'];
                    } else {
                        return $v;
                    }

                    throw new InvalidConfigurationException(sprintf(
                        'The key "entity_class" at path "%s.log" must be configured. It is required by the "%s" service configured at the "%s" config option.',
                        $this->name, $configDetails[0], $configDetails[1]
                    ));
                }

                return $v;
            })
            ->end()
            ->children()
                ->arrayNode('event_processor_services')
                    ->beforeNormalization()
                    ->ifString()->then(function ($v) {
                        return [$v];
                    })
                    ->end()
                    ->prototype('scalar')->end()
                    // todo Add "options" config.
                ->end()
                ->append($this->createLogNode())
                ->append($this->createLogViewingNode())
            ->end();

        return $treeBuilder;
    }


    private function createLogNode()
    {
        $node = $this->createRootNode('log');
        $node
            ->isRequired()
            ->children()
                ->scalarNode('class')
                    ->cannotBeEmpty()->defaultValue(Log::class)
                    ->beforeNormalization()
                    ->ifString()->then(function ($v) {
                        ClassValidator::validateSubclassOf($v, Log::class, 'log');

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
        $node = $this->createRootNode('log_viewing');
        $node
            ->isRequired()
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
                    ->info('Service name of the log driver to use by the LogController to view and manage logs.')
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
                                ->ifString()->then(function ($v) {
                                    return ['class' => $v];
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


    private function createRootNode(string $name, TreeBuilder &$treeBuilder = null): ArrayNodeDefinition
    {
        $treeBuilder = new TreeBuilder($name);

        return method_exists($treeBuilder, 'getRootNode') ? $treeBuilder->getRootNode() : $treeBuilder->root($name);
    }
}
