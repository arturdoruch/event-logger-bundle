<?php

namespace ArturDoruch\EventLoggerBundle;

use ArturDoruch\EventLoggerBundle\Log\LogPropertyCollectionBuilder;
use ArturDoruch\EventLoggerBundle\Log\Property\DateTimeProperty;
use ArturDoruch\EventLoggerBundle\Log\Property\IntegerProperty;
use ArturDoruch\EventLoggerBundle\Log\Property\StringProperty;

/**
 * @author Artur Doruch <arturdoruch@interia.pl>
 */
class Log implements LogInterface
{
    use LogTrait;

    final public function __construct(string $category, string $level, string $action)
    {
        $this->category = $category;
        $this->level = strtolower($level);
        $this->action = $action;
        $this->state = LogStates::NEW;
        $this->createdAt = new \DateTime();
    }


    public static function buildDefaultPropertyCollection(LogPropertyCollectionBuilder $builder)
    {
        $categoryChoices = $builder->getFilterFormConfig('category')['choices'];
        $levelChoices = $builder->getFilterFormConfig('level')['choices'];

        $id = new IntegerProperty('id', 'Id');
        $createdAt = new DateTimeProperty('createdAt', 'Created at');
        $createdAt
            ->listable()
            ->sortable()
            ->filterable();

        $category = new StringProperty('category', 'Category');
        $category
            ->filterable()
            ->setFilterFormChoices(array_combine($categoryChoices, $categoryChoices))
            ->listable();

        $level = new StringProperty('level', 'Level');
        $level
            ->filterable()
            ->setFilterFormChoices(array_combine($levelChoices, $levelChoices))
            ->listable();

        $action = new StringProperty('action', 'Action');

        $state = new StringProperty('state', 'State', 'integer');
        $state
            ->filterable()
            ->setFilterFormChoices(array_flip(LogStates::all()));

        $changedStateAt = new DateTimeProperty('changedStateAt', 'Changed state at');

        $message = new StringProperty('message', 'Message');
        $message
            ->filterable()
            ->listable()
            ->sortable();

        $builder
            ->addProperty($id)
            ->addProperty($category)
            ->addProperty($level)
            ->addProperty($state)
            ->addProperty($changedStateAt)
            ->addProperty($action)
            ->addProperty($message)
            ->addProperty($createdAt);
    }


    public static function buildPropertyCollection(LogPropertyCollectionBuilder $builder)
    {
    }


    public function setProperties(EventInterface $event)
    {
    }


    public function get(string $property)
    {
        return $this->$property;
    }
}
 