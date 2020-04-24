<?php

namespace ArturDoruch\EventLoggerBundle\Templating;

use ArturDoruch\EventLoggerBundle\LogStates;

/**
 * @author Artur Doruch <arturdoruch@interia.pl>
 */
class CssClassHelper
{
    private static $colorClassMap = [
        LogStates::NEW => 'primary',
        LogStates::RESOLVED => 'success',
        LogStates::WATCH => 'info',
    ];

    private static $glyphIconClassMap = [
        LogStates::NEW => 'glyphicon-file',
        LogStates::RESOLVED => 'glyphicon-ok',
        LogStates::WATCH => 'glyphicon-eye-open',
    ];

    /**
     * @param int $logState
     *
     * @return string
     */
    public static function getColorClass(int $logState)
    {
        return self::$colorClassMap[$logState];
    }

    /**
     * @param int $logState
     *
     * @return string
     */
    public static function getGlyphIconClass(int $logState)
    {
        return self::$glyphIconClassMap[$logState];
    }
}
