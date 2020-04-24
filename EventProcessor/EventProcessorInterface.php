<?php

namespace ArturDoruch\EventLoggerBundle\EventProcessor;

use ArturDoruch\EventLoggerBundle\Event;

/**
 * @author Artur Doruch <arturdoruch@interia.pl>
 */
interface EventProcessorInterface
{
    /**
     * Modify event context or category.
     *
     * @param string $level The log level.
     * @param Event $event
     * @param array $options
     */
    public function process(string $level, Event $event, array &$options);
}
