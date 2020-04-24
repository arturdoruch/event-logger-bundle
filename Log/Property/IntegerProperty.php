<?php

namespace ArturDoruch\EventLoggerBundle\Log\Property;

/**
 * @author Artur Doruch <arturdoruch@interia.pl>
 */
class IntegerProperty extends AbstractProperty
{
    public function getType(): string
    {
        return 'integer';
    }
}
