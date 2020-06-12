<?php

namespace ArturDoruch\EventLoggerBundle\Log\Handler;

use ArturDoruch\EventLoggerBundle\Entity\LogManager;
use ArturDoruch\EventLoggerBundle\LogInterface;

/**
 * @author Artur Doruch <arturdoruch@interia.pl>
 */
class DatabaseLogHandler implements LogHandlerInterface
{
    /**
     * @var LogManager
     */
    protected $logManager;

    public function __construct(LogManager $logManager)
    {
        $this->logManager = $logManager;
    }

    /**
     * @param LogInterface $log
     *
     * @return string The log id.
     */
    public function handle(LogInterface $log)
    {
        return $this->logManager->insert($log);
    }
}
