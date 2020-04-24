<?php

namespace ArturDoruch\EventLoggerBundle\Templating\LogContext\ObjectConverter;

/**
 * @author Artur Doruch <arturdoruch@interia.pl>
 */
class ObjectConverterManager
{
    /**
     * @var ObjectConverterInterface[]
     */
    private $converters = [];

    public function add(ObjectConverterInterface $converter)
    {
        $this->converters[] = $converter;
    }

    /**
     * Converts object to an array or scalar value.
     *
     * @param object $object
     *
     * @return mixed
     */
    public function toArrayOrScalar($object)
    {
        foreach ($this->converters as $converter) {
            if (!$converter->supports($object)) {
                continue;
            }

            $value = $converter->toArrayOrScalar($object);

            if (!is_array($value) && !is_scalar($value)) {
                throw new \InvalidArgumentException(sprintf(
                    'The object converter method "%s::toArrayOrScalar" must return an array or scalar value, but "%s" returned.',
                    get_class($converter), gettype($value)
                ));
            }

            return $value;
        }

        throw new \RuntimeException(sprintf(
            'Not found object converter supporting the "%s" object.', get_class($object)
        ));
    }
}
