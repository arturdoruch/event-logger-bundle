<?php

namespace ArturDoruch\EventLoggerBundle\EventProcessor;

use ArturDoruch\EventLoggerBundle\Event;
use ArturDoruch\Tool\Exception\ExceptionFormatter;
use ArturDoruch\Util\Json\UnexpectedJsonException;

/**
 * @author Artur Doruch <arturdoruch@interia.pl>
 */
class ExceptionProcessor implements EventProcessorInterface
{
    /**
     * @var ExceptionFormatter
     */
    protected $exceptionFormatter;


    public function __construct(ExceptionFormatter $exceptionFormatter)
    {
        $this->exceptionFormatter = $exceptionFormatter;
    }


    public function process(string $level, Event $event, array &$options)
    {
        if (!$e = $event->getThrowable()) {
            return;
        }

        $context = $event->getContext();

        $exceptionContext = [
            'class' => get_class($e),
            'file' => $this->exceptionFormatter->shortenFilename($e->getFile()) . ' line ' . $e->getLine(),
        ];

        if (($options['exception_trace'] ?? false) === true) {
            $exceptionContext['trace'] = $this->exceptionFormatter->getTraceAsHtml($e);
        }

        if (class_exists('\ArturDoruch\Util\Json\UnexpectedJsonException') && $e instanceof UnexpectedJsonException) {
            $exceptionContext['json'] = strlen($json = $e->getJson()) > 5000 ? substr($e->getJson(), 0, 5000) . '...' : $json;
        }

        $context = array_merge(['exception' => $exceptionContext], $context);
        $event->setContext($context);
    }
}
