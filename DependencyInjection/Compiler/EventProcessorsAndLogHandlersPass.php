<?php

namespace ArturDoruch\EventLoggerBundle\DependencyInjection\Compiler;

use ArturDoruch\ClassValidator\ClassValidator;
use ArturDoruch\EventLoggerBundle\EventProcessor\EventProcessorInterface;
use ArturDoruch\EventLoggerBundle\Log\Handler\LogHandlerInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @author Artur Doruch <arturdoruch@interia.pl>
 */
class EventProcessorsAndLogHandlersPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $eventLoggerDef = $container->getDefinition('arturdoruch_eventlogger.event_logger');
        // Add event processors
        $eventProcessorServices = $container->getParameter('arturdoruch_eventlogger.event_processor_services');

        foreach ($eventProcessorServices as $service) {
            $eventProcessor = $container->getDefinition($service);
            ClassValidator::validateImplementsInterface($eventProcessor->getClass(), EventProcessorInterface::class, 'event processor');
            $eventLoggerDef->addMethodCall('addEventProcessor', [$eventProcessor]);
        }

        // Add log handlers
        $loggerListenerServices = $container->getParameter('arturdoruch_eventlogger.log.handler_services');

        foreach ($loggerListenerServices as $service) {
            $logHandler = $container->getDefinition($service);
            ClassValidator::validateImplementsInterface($logHandler->getClass(), LogHandlerInterface::class, 'event log handler');
            $eventLoggerDef->addMethodCall('addLogHandler', [$logHandler]);
        }
    }
}
 