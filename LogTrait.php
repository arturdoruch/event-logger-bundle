<?php

namespace ArturDoruch\EventLoggerBundle;

/**
 * The event log traits.
 *
 * @author Artur Doruch <arturdoruch@interia.pl>
 */
trait LogTrait
{
    /**
     * @var mixed
     */
    protected $id;

    /**
     * @var \DateTime
     */
    protected $createdAt;

    /**
     * @var string The event category.
     */
    protected $category;

    /**
     * @var string The log level.
     */
    protected $level;

    /**
     * @var string The event action.
     */
    protected $action;

    /**
     * @var string
     */
    protected $message;

    /**
     * @var array
     */
    protected $context = [];

    /**
     * @var int The log state.
     */
    protected $state;

    /**
     * @var \DateTime
     */
    protected $changedStateAt;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     *
     * @return $this
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param \DateTime $createdAt
     *
     * @return $this
     */
    public function setCreatedAt(\DateTime $createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * @return string
     */
    public function getCategory(): string
    {
        return $this->category;
    }

    /**
     * @param string $category
     *
     * @return $this
     */
    public function setCategory(string $category)
    {
        $this->category = $category;

        return $this;
    }

    /**
     * @return string
     */
    public function getLevel(): string
    {
        return $this->level;
    }

    /**
     * @return string
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * @param string $action
     *
     * @return $this
     */
    public function setAction($action)
    {
        $this->action = $action;

        return $this;
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @param string $message
     *
     * @return $this
     */
    public function setMessage($message)
    {
        $this->message = $message;

        return $this;
    }

    /**
     * @return array
     */
    public function getContext(): array
    {
        return $this->context;
    }

    /**
     * @param array $context
     *
     * @return $this
     */
    public function setContext(array $context)
    {
        $this->context = $context;

        return $this;
    }

    /**
     * @return int
     */
    public function getState(): int
    {
        return $this->state;
    }

    /**
     * @param int $state
     *
     * @return $this
     */
    public function setState(int $state)
    {
        $this->state = $state;

        return $this;
    }

    /**
     * Gets log state string representation.
     *
     * @return string
     */
    public function getStateString()
    {
        return LogStates::toString($this->state);
    }

    /**
     * @return \DateTime
     */
    public function getChangedStateAt(): ?\DateTime
    {
        return $this->changedStateAt;
    }

    /**
     * @param \DateTime $changedStateAt
     *
     * @return $this
     */
    public function setChangedStateAt(\DateTime $changedStateAt)
    {
        $this->changedStateAt = $changedStateAt;

        return $this;
    }
}
