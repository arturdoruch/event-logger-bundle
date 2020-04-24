<?php

namespace ArturDoruch\EventLoggerBundle\Log\Handler;

use ArturDoruch\EventLoggerBundle\LogInterface;

/**
 * @author Artur Doruch <arturdoruch@interia.pl>
 */
interface LogHandlerInterface
{
    /**
     * @param LogInterface $log
     */
    public function handle(LogInterface $log);
}
