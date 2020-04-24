<?php

namespace ArturDoruch\EventLoggerBundle\Log\Driver\Exception;

/**
 * @author Artur Doruch <arturdoruch@interia.pl>
 */
class LogNotFoundException extends \RuntimeException
{
    /**
     * @param string|int $id The log id.
     */
    public function __construct($id)
    {
        $message = sprintf('Log with id "%s" was not found.', $id);

        parent::__construct($message);
    }
}
