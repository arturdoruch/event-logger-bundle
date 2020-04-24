<?php

namespace ArturDoruch\EventLoggerBundle\Templating\LogContext\ObjectConverter;

/**
 * Converts log context object.
 *
 * @author Artur Doruch <arturdoruch@interia.pl>
 */
interface ObjectConverterInterface
{
    /**
     * Checks if the converter supports specific object.
     *
     * @param object $object
     *
     * @return bool
     */
    public function supports($object): bool;

    /**
     * Converts object to an array or scalar value.
     *
     * @param object $object
     *
     * @return mixed
     */
    public function toArrayOrScalar($object);
}
