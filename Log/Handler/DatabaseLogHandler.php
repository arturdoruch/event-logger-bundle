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


    public function handle(LogInterface $log)
    {
        $this->logManager->insert($log);
    }
}
