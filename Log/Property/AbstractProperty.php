<?php

namespace ArturDoruch\EventLoggerBundle\Log\Property;

/**
 * @author Artur Doruch <arturdoruch@interia.pl>
 */
abstract class AbstractProperty implements PropertyInterface
{
    /**
     * @var bool Whether the property is an additional log property.
     */
    private $extra = false;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string The name used in the log list and log view.
     */
    private $label;

    /**
     * @var bool Whether to property should be added to the log filter form.
     */
    private $filterable = false;

    /**
     * @var bool Whether the column with this property should be displayed on the log list.
     */
    private $listable = false;

    /**
     * @var bool
     */
    private $sortable = false;

    /**
     * @var string
     */
    private $sortingDefaultDirection = 'asc';

    /**
     * @var array|null
     */
    private $filterFormChoices;

    /**
     * @var bool
     */
    private $filterFormChoicePlaceholder = true;


    public function __construct(string $name, string $label)
    {
        $this->name = $name;
        $this->label = $label;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getLabel(): string
    {
        return $this->label;
    }


    public function isType(string $type): bool
    {
        return $this->getType() === $type;
    }

    /**
     * Allow property to be added to the log filter form.
     *
     * @param bool $value
     *
     * @return $this
     */
    public function filterable(bool $value = true)
    {
        $this->filterable = $value;

        return $this;
    }

    /**
     * @return boolean
     */
    public function isFilterable(): bool
    {
        return $this->filterable;
    }

    /**
     * Allow property to be displayed on the log list.
     *
     * @param bool $value
     *
     * @return $this
     */
    public function listable(bool $value = true)
    {
        $this->listable = $value;

        return $this;
    }

    /**
     * @return boolean
     */
    public function isListable(): bool
    {
        return $this->listable;
    }

    /**
     * @param bool $value
     * @param string $defaultDirection
     *
     * @return $this
     */
    public function sortable(bool $value = true, $defaultDirection = 'asc')
    {
        $this->sortable = $value;
        $this->sortingDefaultDirection = $defaultDirection;

        return $this;
    }

    /**
     * @return boolean
     */
    public function isSortable(): bool
    {
        return $this->sortable;
    }

    /**
     * @return string
     */
    public function getSortingDefaultDirection(): string
    {
        return $this->sortingDefaultDirection;
    }

    /**
     * @param array $choices
     * @param bool $addPlaceholder Whether to add an empty value at the top to the select field choices.
     *
     * @return $this
     */
    public function setFilterFormChoices(array $choices, bool $addPlaceholder = true)
    {
        $this->filterFormChoices = $choices;
        $this->filterFormChoicePlaceholder = $addPlaceholder;

        return $this;
    }

    /**
     * @return array|null
     */
    public function getFilterFormChoices(): ?array
    {
        return $this->filterFormChoices;
    }

    /**
     * @return boolean
     */
    public function isFilterFormChoicePlaceholderRequired(): bool
    {
        return $this->filterFormChoicePlaceholder;
    }

    /**
     * @return boolean
     */
    public function isExtra(): bool
    {
        return $this->extra;
    }

    /**
     * Marks property as additional.
     */
    public function extra()
    {
        $this->extra = true;
    }
}
