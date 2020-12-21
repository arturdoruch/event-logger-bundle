<?php

namespace ArturDoruch\EventLoggerBundle\DependencyInjection;

use ArturDoruch\EventLoggerBundle\Log\LogMetadata;
use ArturDoruch\EventLoggerBundle\Log\LogPropertyCollection;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * @author Artur Doruch <arturdoruch@interia.pl>
 */
class ArturDoruchEventLoggerExtension extends Extension
{

    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');

        $logConfig = $config['log'];
        $logViewingConfig = $config['log_viewing'];

        $container->setParameter('arturdoruch_eventlogger.event_processor_services', $config['event_processor_services']);

        $container->setParameter('arturdoruch_eventlogger.log.entity_class', $logConfig['entity_class']);
        $container->setParameter('arturdoruch_eventlogger.log.handler_services', $logConfig['handler_services']);

        $container->setParameter('arturdoruch_eventlogger.log.driver_service', $logViewingConfig['driver_service']);

        $container->setParameter('arturdoruch_eventlogger.templating.base_template', $logViewingConfig['base_template']);
        $container->setParameter('arturdoruch_eventlogger.templating.log_context.object_converters', $logViewingConfig['context']['object_converters'] ?? []);
        $container->setParameter('arturdoruch_eventlogger.templating.log_context.value_formatters', $logViewingConfig['context']['value_formatters'] ?? []);

        $container->setParameter('arturdoruch_eventlogger.log.filter_form.choice_placeholder', $logViewingConfig['filter_form']['choice_placeholder']);
        $container->setParameter('arturdoruch_eventlogger.log.list_item_limits', $logViewingConfig['list_item_limits']);

        $this->loadLogMetadata($logConfig, $logViewingConfig, $container);
    }


    private function loadLogMetadata(array $logConfig, array $logViewingConfig, ContainerBuilder $container)
    {
        $filterFormConf = $logViewingConfig['filter_form'];
        $filterFormConfig = [
            'category' => [
                'choices' => $filterFormConf['category_choices']
            ],
            'level' => [
                'choices' => $filterFormConf['level_choices']
            ],
        ];

        $metadataDefinition = new Definition(LogMetadata::class, [$logConfig['class'], $filterFormConfig]);
        $propertyCollectionDefinition = new Definition(LogPropertyCollection::class);
        $propertyCollectionDefinition->setFactory([$metadataDefinition, 'getPropertyCollection']);

        $container->setDefinition('arturdoruch_eventlogger.log_metadata', $metadataDefinition);
        $container->setDefinition('arturdoruch_eventlogger.log_property_collection', $propertyCollectionDefinition);
    }
}
