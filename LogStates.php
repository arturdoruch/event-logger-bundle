<?php

namespace ArturDoruch\EventLoggerBundle;

/**
 * @author Artur Doruch <arturdoruch@interia.pl>
 */
class LogStates
{
    /**
     * The log is fresh, not viewed.
     */
    const NEW = 0;

    /**
     * The log has been reviewed and eventually error has been fixed.
     */
    const RESOLVED = 1;

    /**
     * Interesting log to watch.
     */
    const WATCH = 2;

    private static $states = [
        self::NEW => 'new',
        self::RESOLVED => 'resolved',
        self::WATCH => 'watch'
    ];

    /**
     * Gets the array with log states, where keys are states in integer, and values in string representation.
     *
     * @return array
     */
    public static function all(): array
    {
        return self::$states;
    }

    /**
     * Gets integer state in string representation.
     *
     * @param int $state
     *
     * @return string
     */
    public static function toString(int $state): string
    {
        return self::$states[$state];
    }
}
