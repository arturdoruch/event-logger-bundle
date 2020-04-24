<?php

namespace ArturDoruch\EventLoggerBundle\Log\Property;

/**
 * The log property interface.
 *
 * @author Artur Doruch <arturdoruch@interia.pl>
 */
interface PropertyInterface
{
    /**
     * @return boolean
     */
    public function isExtra(): bool;

    /**
     * Marks property as extra.
     */
    public function extra();

    /**
     * @return string
     */
    public function getName(): string;

    /**
     * Gets property label used in the log list and log view.
     *
     * @return string
     */
    public function getLabel(): string;

    /**
     * @return string
     */
    public function getType(): string;

    public function isType(string $type): bool;

    /**
     * Allow property to be added to the log filter form.
     *
     * @param bool $value
     *
     * @return $this
     */
    public function filterable(bool $value = true);

    public function isFilterable(): bool;

    /**
     * Allow property to be displayed on the log list.
     *
     * @param bool $value
     *
     * @return $this
     */
    public function listable(bool $value = true);

    public function isListable(): bool;

    /**
     * @param bool $value
     * @param string $defaultDirection
     *
     * @return $this
     */
    public function sortable(bool $value = true, $defaultDirection = 'asc');

    public function isSortable(): bool;

    /**
     * @return string
     */
    public function getSortingDefaultDirection(): string;

    /**
     * @param array $choices
     * @param bool $addPlaceholder Whether to add an empty value at the top to the select field choices.
     *
     * @return $this
     */
    public function setFilterFormChoices(array $choices, bool $addPlaceholder = true);

    /**
     * @return array|null
     */
    public function getFilterFormChoices(): ?array;

    public function isFilterFormChoicePlaceholderRequired(): bool;
}
