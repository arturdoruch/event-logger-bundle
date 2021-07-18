<?php

namespace ArturDoruch\EventLoggerBundle\EventProcessor;

use ArturDoruch\EventLoggerBundle\Event;

/**
 * @author Artur Doruch <arturdoruch@interia.pl>
 */
interface EventProcessorInterface
{
    /**
     * Allows to modify the event context and category, and event processor options
     * depend on the event properties.
     *
     * @param string $level The log level.
     * @param Event $event
     * @param array $options
     */
    public function process(string $level, Event $event, array &$options);
}
