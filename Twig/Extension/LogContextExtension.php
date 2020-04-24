<?php

namespace ArturDoruch\EventLoggerBundle\Twig\Extension;

use ArturDoruch\EventLoggerBundle\Templating\LogContext\ObjectConverter\ObjectConverterManager;
use ArturDoruch\EventLoggerBundle\Templating\LogContext\ValueFormatter\ValueFormatterManager;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * @author Artur Doruch <arturdoruch@interia.pl>
 */
class LogContextExtension extends AbstractExtension
{
    /**
     * @var ObjectConverterManager
     */
    private $objectConverterManager;

    /**
     * @var ValueFormatterManager
     */
    private $valueFormatterManager;


    public function __construct(ObjectConverterManager $objectConverterManager, ValueFormatterManager $valueFormatterManager)
    {
        $this->objectConverterManager = $objectConverterManager;
        $this->valueFormatterManager = $valueFormatterManager;
    }


    public function getFunctions()
    {
        return [
            new TwigFunction('arturdoruch_eventlogger_convert_object', [$this, 'convertObject']),
            new TwigFunction('arturdoruch_eventlogger_format_value', [$this, 'formatValue']),
        ];
    }

    /**
     * Converts object to an array or scalar value.
     *
     * @param object $object
     *
     * @return mixed
     */
    public function convertObject($object)
    {
        if (!is_object($object)) {
            return $object;
        }

        return $this->objectConverterManager->toArrayOrScalar($object);
    }


    public function formatValue($name, $value)
    {
        return $this->valueFormatterManager->format($name, $value);
    }
}
