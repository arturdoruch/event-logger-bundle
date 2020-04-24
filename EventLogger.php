<?php

namespace ArturDoruch\EventLoggerBundle;

use ArturDoruch\EventLoggerBundle\EventProcessor\EventProcessorInterface;
use ArturDoruch\EventLoggerBundle\Log\Handler\LogHandlerInterface;
use ArturDoruch\EventLoggerBundle\Log\LogMetadata;

/**
 * @author Artur Doruch <arturdoruch@interia.pl>
 */
class EventLogger
{
    /**
     * @var string
     */
    private $logClass;

    /**
     * @var EventProcessorInterface[]
     */
    private $eventProcessors = [];

    /**
     * @var LogHandlerInterface[]
     */
    private $logHandlers = [];

    /**
     * @var array
     */
    private $defaultContext = [];


    public function __construct(LogMetadata $logMetadata)
    {
        $this->logClass = $logMetadata->getClassName();
    }


    public function addEventProcessor(EventProcessorInterface $eventProcessor)
    {
        $this->eventProcessors[] = $eventProcessor;
    }


    public function addLogHandler(LogHandlerInterface $logHandler)
    {
        $this->logHandlers[] = $logHandler;
    }

    /**
     * @param array $context
     */
    public function setDefaultContext(array $context)
    {
        $this->defaultContext = $context;
    }


    public function unsetDefaultContext()
    {
        $this->defaultContext = [];
    }


    public function log(string $level, Event $event, array $processorOptions = [])
    {
        foreach ($this->defaultContext as $name => $value) {
            $event->addContext($name, $value);
        }

        foreach ($this->eventProcessors as $processor) {
            $processor->process($level, $event, $processorOptions);
        }

        $log = $this->createLog($level, $event);
        $log->setProperties($event);

        foreach ($this->logHandlers as $handler) {
            $handler->handle($log);
        }
    }


    private function createLog(string $level, Event $event): LogInterface
    {
        /** @var LogInterface $log */
        $log = new $this->logClass($event->getCategory(), $level, $event->getAction());
        $log
            ->setMessage($event->getMessage())
            ->setContext($event->getContext());

        return $log;
    }
}
