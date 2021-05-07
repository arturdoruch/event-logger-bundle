<?php

namespace ArturDoruch\EventLoggerBundle\DependencyInjection\Compiler;

use ArturDoruch\ClassValidator\ClassValidator;
use ArturDoruch\EventLoggerBundle\Log\Driver\LogDriverInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @author Artur Doruch <arturdoruch@interia.pl>
 */
class LogDriverPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $serviceId = $container->getParameter('arturdoruch_eventlogger.log.driver_service');
        $definition = $container->getDefinition($serviceId);
        ClassValidator::validateImplementsInterface($definition->getClass(), LogDriverInterface::class, 'log driver');
        $container->setAlias('arturdoruch_eventlogger.log_driver', $serviceId);
    }
}
 