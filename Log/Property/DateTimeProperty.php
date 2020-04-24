<?php

namespace ArturDoruch\EventLoggerBundle\Log\Property;

/**
 * @author Artur Doruch <arturdoruch@interia.pl>
 */
class DateTimeProperty extends AbstractProperty
{
    /**
     * @var string Format used to formatting date in the list and detail log views.
     */
    private $format = 'd.m.Y H:i:s';

    /**
     * @var string The Symfony form date type format.
     */
    private $filterFormFormat = 'dd.MM.yyyy';


    public function __construct(string $name, string $label, string $format = 'd.m.Y H:i:s')
    {
        parent::__construct($name, $label);
        $this->format = $format;
    }


    public function getType(): string
    {
        return 'datetime';
    }

    /**
     * @param string $format
     */
    public function setFormat(string $format)
    {
        $this->format = $format;
    }

    /**
     * @return string
     */
    public function getFormat(): string
    {
        return $this->format;
    }

    /**
     * @return string
     */
    public function getFilterFormFormat(): string
    {
        return $this->filterFormFormat;
    }

    /**
     * @param string $filterFormFormat
     *
     * @return $this
     */
    public function setFilterFormFormat(string $filterFormFormat)
    {
        $this->filterFormFormat = $filterFormFormat;

        return $this;
    }
}
