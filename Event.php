<?php

namespace ArturDoruch\EventLoggerBundle;

/**
 * @author Artur Doruch <arturdoruch@interia.pl>
 */
class Event implements EventInterface
{
    /**
     * @var string
     */
    private $category;

    /**
     * @var string
     */
    private $action;

    /**
     * @var string
     */
    private $message;

    /**
     * @var \Throwable
     */
    private $throwable;

    /**
     * @var array
     */
    private $context;

    /**
     * @param string|\Throwable $message The event message or \Throwable instance.
     * @param string $action Custom action name while event occur.
     * @param string $category
     * @param array $context
     */
    public function __construct($message, string $action, ?string $category = null, array $context = [])
    {
        if ($message instanceof \Throwable) {
            $this->throwable = $message;
            $this->message = $message->getMessage();
        } else {
            $this->message = $message;
        }

        $this->action = $action;
        $this->category = $category;
        $this->context = $context;
    }

    /**
     * @return string
     */
    public function getCategory(): ?string
    {
        return $this->category;
    }

    /**
     * @param string $category
     */
    public function setCategory(string $category)
    {
        $this->category = $category;
    }

    /**
     * @return string
     */
    public function getAction(): string
    {
        return $this->action;
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @return \Throwable|null
     */
    public function getThrowable()
    {
        return $this->throwable;
    }

    /**
     * @return bool
     */
    public function hasThrowable()
    {
        return $this->throwable !== null;
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
     */
    public function setContext(array $context)
    {
        $this->context = $context;
    }

    /**
     * @param string $name
     * @param mixed $value
     *
     * @return $this
     */
    public function addContext(string $name, $value)
    {
        $this->context[$name] = $value;

        return $this;
    }
}
