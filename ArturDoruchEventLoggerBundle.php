<?php

namespace ArturDoruch\EventLoggerBundle;

use ArturDoruch\EventLoggerBundle\DependencyInjection\Compiler\EventProcessorsAndLogHandlersPass;
use ArturDoruch\EventLoggerBundle\DependencyInjection\Compiler\LogContextObjectConvertersPass;
use ArturDoruch\EventLoggerBundle\DependencyInjection\Compiler\LogContextValueFormattersPass;
use ArturDoruch\EventLoggerBundle\DependencyInjection\Compiler\LogDriverPass;
use ArturDoruch\EventLoggerBundle\Form\LogFilterType;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * @author Artur Doruch <arturdoruch@interia.pl>
 */
class ArturDoruchEventLoggerBundle extends Bundle
{
    public function boot()
    {
        if ($this->container->hasParameter('arturdoruch_eventlogger.log.filter_form.choice_placeholder')) {
            LogFilterType::$choicePlaceholder = $this->container->getParameter('arturdoruch_eventlogger.log.filter_form.choice_placeholder');
        }
    }

    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new EventProcessorsAndLogHandlersPass());
        $container->addCompilerPass(new LogDriverPass());
        $container->addCompilerPass(new LogContextObjectConvertersPass());
        $container->addCompilerPass(new LogContextValueFormattersPass());
    }
}
