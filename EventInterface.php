<?php

namespace ArturDoruch\EventLoggerBundle;

/**
 * @author Artur Doruch <arturdoruch@interia.pl>
 */
interface EventInterface
{
    /**
     * @return string
     */
    public function getCategory(): ?string;

    /**
     * @param string $category
     */
    public function setCategory(string $category);

    /**
     * @return string
     */
    public function getAction(): string;

    /**
     * @return string
     */
    public function getMessage();

    /**
     * @return \Throwable|null
     */
    public function getThrowable();

    /**
     * @return bool
     */
    public function hasThrowable();

    /**
     * @return array
     */
    public function getContext(): array;

    /**
     * @param array $context
     */
    public function setContext(array $context);

    /**
     * @param string $name
     * @param mixed $value
     *
     * @return $this
     */
    public function addContext(string $name, $value);
}
