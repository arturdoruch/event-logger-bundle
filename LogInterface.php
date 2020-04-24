<?php

namespace ArturDoruch\EventLoggerBundle;

use ArturDoruch\EventLoggerBundle\Log\LogPropertyCollectionBuilder;

/**
 * @author Artur Doruch <arturdoruch@interia.pl>
 */
interface LogInterface
{
    /**
     * @param LogPropertyCollectionBuilder $builder
     */
    public static function buildPropertyCollection(LogPropertyCollectionBuilder $builder);

    /**
     * Sets log extra properties.
     *
     * @param EventInterface $event
     */
    public function setProperties(EventInterface $event);

    /**
     * @return mixed
     */
    public function getId();

    /**
     * @param mixed $id
     *
     * @return $this
     */
    public function setId($id);

    /**
     * @return \DateTime
     */
    public function getCreatedAt();

    /**
     * @param \DateTime $createdAt
     *
     * @return $this
     */
    public function setCreatedAt(\DateTime $createdAt);

    /**
     * @return string
     */
    public function getLevel(): string;

    /**
     * @return string
     */
    public function getCategory(): string;

    /**
     * @param string $category
     *
     * @return $this
     */
    public function setCategory(string $category);

    /**
     * @return string
     */
    public function getAction();

    /**
     * @return string
     */
    public function getMessage();

    /**
     * @param string $message
     *
     * @return $this
     */
    public function setMessage($message);

    /**
     * @return array
     */
    public function getContext(): array;

    /**
     * @param array $context
     *
     * @return $this
     */
    public function setContext(array $context);

    /**
     * @return int
     */
    public function getState(): int;

    /**
     * @param int $state
     *
     * @return $this
     */
    public function setState(int $state);

    /**
     * Gets log state string representation.
     *
     * @return string
     */
    public function getStateString();

    /**
     * @return \DateTime
     */
    public function getChangedStateAt(): ?\DateTime;

    /**
     * @param \DateTime $changedStateAt
     *
     * @return $this
     */
    public function setChangedStateAt(\DateTime $changedStateAt);

    /**
     * Gets property value.
     *
     * @param string $property The log property name.
     *
     * @return mixed
     */
    public function get(string $property);
}
