<?php

namespace ArturDoruch\EventLoggerBundle\Log;

use ArturDoruch\EventLoggerBundle\Log\Property\DateTimeProperty;
use ArturDoruch\EventLoggerBundle\Log\Property\PropertyInterface;
use ArturDoruch\EventLoggerBundle\Log\Property\StringProperty;
use ArturDoruch\EventLoggerBundle\LogInterface;

/**
 * @author Artur Doruch <arturdoruch@interia.pl>
 */
class LogPropertyCollection implements \IteratorAggregate
{
    /**
     * @var PropertyInterface[]
     */
    private $properties = [];

    /**
     * @param PropertyInterface $property
     *
     * @return $this
     */
    public function add(PropertyInterface $property)
    {
        $this->properties[$property->getName()] = $property;

        return $this;
    }

    /**
     * @param string $name
     *
     * @return PropertyInterface
     * @throws \InvalidArgumentException
     */
    public function get(string $name): PropertyInterface
    {
        if (!isset($this->properties[$name])) {
            throw new \InvalidArgumentException(sprintf('The log property with name "%s" does not exist.', $name));
        }

        return $this->properties[$name];
    }

    /**
     * @return StringProperty[]
     */
    public function getListable(): array
    {
        return array_filter($this->properties, function (PropertyInterface $property) {
            return $property->isListable();
        });
    }

    /**
     * @return StringProperty[]
     */
    public function getFilterable(): array
    {
        return array_filter($this->properties, function (PropertyInterface $property) {
            return $property->isFilterable();
        });
    }

    /**
     * @return StringProperty[]
     */
    public function getExtra(): array
    {
        return array_filter($this->properties, function (PropertyInterface $property) {
            return $property->isExtra();
        });
    }

    /**
     * @return StringProperty[]
     */
    public function all(): array
    {
        return $this->properties;
    }


    public function getIterator(): \ArrayIterator
    {
        return new \ArrayIterator($this->properties);
    }

    /**
     * Converts log properties into array.
     *
     * @param LogInterface $log
     * @return array
     */
    public function logToArray(LogInterface $log): array
    {
        $properties = [];

        foreach ($this->properties as $property) {
            $name = $property->getName();
            $value = $log->get($name);

            if ($property instanceof DateTimeProperty) {
                $value = $value instanceof \DateTime ? $value->format($property->getFormat()) : null;
            }

            $properties[$name] = $value;
        }

        $properties['stateString'] = $log->getStateString();

        return $properties;
    }
}
