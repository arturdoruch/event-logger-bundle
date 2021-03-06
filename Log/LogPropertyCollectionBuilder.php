<?php

namespace ArturDoruch\EventLoggerBundle\Log;

use ArturDoruch\EventLoggerBundle\Log\Property\PropertyInterface;

/**
 * @author Artur Doruch <arturdoruch@interia.pl>
 */
class LogPropertyCollectionBuilder
{
    /**
     * @var PropertyInterface[]
     */
    private $properties = [];

    /**
     * @var array
     */
    private $filterFormConfig = [];

    /**
     * @var bool
     */
    private $propertyExtraType = false;


    public function __construct(array $filterFormConfig)
    {
        $this->filterFormConfig = $filterFormConfig;
    }

    /**
     * @param string $property The log property name.
     *
     * @return array
     *  - choices
     */
    public function getFilterFormConfig(string $property): array
    {
        return $this->filterFormConfig[$property];
    }

    /**
     * @param boolean $propertyExtraType
     */
    public function setPropertyExtraType(bool $propertyExtraType)
    {
        $this->propertyExtraType = $propertyExtraType;
    }

    /**
     * @param PropertyInterface $property
     * @param string $previousProperty The property name in the collection, after which new property should be added.
     *
     * @return $this
     */
    public function addProperty(PropertyInterface $property, string $previousProperty = null)
    {
        if ($this->propertyExtraType) {
            $property->extra();
        }

        if ($previousProperty && ($index = $this->getPropertyIndex($previousProperty))) {
            // Insert property into properties array at specific position.
            array_splice($this->properties, ++$index, 0, [$property]);
        } else {
            $this->properties[] = $property;
        }

        return $this;
    }

    /**
     * @param string $name
     *
     * @return PropertyInterface
     * @throws \InvalidArgumentException when log property with specified name does not exist.
     */
    public function getProperty(string $name): PropertyInterface
    {
        foreach ($this->properties as $property) {
            if ($property->getName() === $name) {
                return $property;
            }
        }

        throw new \InvalidArgumentException(sprintf('The log property with name "%s" does not exist.', $name));
    }


    private function getPropertyIndex(string $property): ?int
    {
        foreach ($this->properties as $index => $prop) {
            if ($prop->getName() === $property) {
                return $index;
            }
        }

        return null;
    }


    public function create(): LogPropertyCollection
    {
        $collection = new LogPropertyCollection();

        foreach ($this->properties as $property) {
            $collection->add($property);
        }

        return $collection;
    }
}
