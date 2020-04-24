<?php

namespace ArturDoruch\EventLoggerBundle\Templating\LogContext\ObjectConverter;

/**
 * @author Artur Doruch <arturdoruch@interia.pl>
 */
class DefaultObjectConverter implements ObjectConverterInterface
{
    public function supports($object): bool
    {
        return is_object($object);
    }


    public function toArrayOrScalar($object)
    {
        $reflection = new \ReflectionClass($object);
        $methods = $reflection->getMethods(\ReflectionMethod::IS_PUBLIC | !\ReflectionMethod::IS_STATIC);
        $properties = [];

        foreach ($methods as $method) {
            $name = $method->getName();

            if (!preg_match('/^(get|is|has)([A-Z\d])(.+)$/', $name, $matches) || $method->getNumberOfRequiredParameters() > 0) {
                continue;
            }

            $formattedName = ($matches[1] === 'get') ? '' : $matches[1];
            $formattedName .= $matches[2] . $matches[3];
            $properties[$formattedName] = $object->$name();
        }

        if (empty($properties) && $reflection->hasMethod('__toString')) {
            return $object->__toString();
        }

        return $properties;
    }
}
