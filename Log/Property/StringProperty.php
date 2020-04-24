<?php

namespace ArturDoruch\EventLoggerBundle\Log\Property;

/**
 * @author Artur Doruch <arturdoruch@interia.pl>
 */
class StringProperty extends AbstractProperty
{
    public function getType(): string
    {
        return 'string';
    }
}
