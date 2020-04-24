<?php

namespace ArturDoruch\EventLoggerBundle\DependencyInjection\Compiler;

use ArturDoruch\EventLoggerBundle\Templating\LogContext\ObjectConverter\DefaultObjectConverter;
use ArturDoruch\EventLoggerBundle\Templating\LogContext\ObjectConverter\ObjectConverterInterface;
use ArturDoruch\EventLoggerBundle\Templating\LogContext\ObjectConverter\ObjectConverterManager;
use ArturDoruch\Tool\ClassValidator;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

/**
 * @author Artur Doruch <arturdoruch@interia.pl>
 */
class LogContextObjectConvertersPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $managerDefinition = new Definition(ObjectConverterManager::class, [$container->getDefinition('twig')]);
        $container->setDefinition('arturdoruch_eventlogger.templating.log_context.object_converter_manager', $managerDefinition);

        $converterClassNames = $container->getParameter('arturdoruch_eventlogger.templating.log_context.object_converters');

        foreach ($converterClassNames as $className) {
            ClassValidator::validateImplementsInterface($className, ObjectConverterInterface::class, 'log context object converter');
            $definition = new Definition($className);
            $managerDefinition->addMethodCall('add', [$definition]);
        }

        // Add default object converter as the last converter.
        $defaultDefinition = new Definition(DefaultObjectConverter::class);
        $managerDefinition->addMethodCall('add', [$defaultDefinition]);
    }
}
 