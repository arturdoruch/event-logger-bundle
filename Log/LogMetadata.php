<?php

namespace ArturDoruch\EventLoggerBundle\Log;

use ArturDoruch\EventLoggerBundle\Log;
use ArturDoruch\ClassValidator\ClassValidator;

/**
 * @author Artur Doruch <arturdoruch@interia.pl>
 */
class LogMetadata
{
    /**
     * @var string
     */
    private $className;

    /**
     * @var LogPropertyCollection
     */
    private $propertyCollection;

    /**
     * @param string $className The log class name.
     * @param array $filterFormConfig
     */
    public function __construct(string $className, array $filterFormConfig)
    {
        $this->className = $className;
        ClassValidator::validateSubclassOf($className, Log::class, 'log');

        $builder = new LogPropertyCollectionBuilder($filterFormConfig);

        Log::buildDefaultPropertyCollection($builder);
        $builder->setPropertyExtraType(true);
        $className::buildPropertyCollection($builder);

        $this->propertyCollection = $builder->create();
    }

    /**
     * @return string
     */
    public function getClassName(): string
    {
        return $this->className;
    }

    /**
     * @return LogPropertyCollection
     */
    public function getPropertyCollection(): LogPropertyCollection
    {
        return $this->propertyCollection;
    }
}
