<?php

namespace ArturDoruch\EventLoggerBundle\EventProcessor;

use ArturDoruch\EventLoggerBundle\Event;
use ArturDoruch\Tool\ExceptionFormatter\ExceptionFormatter;
use ArturDoruch\Util\Json\UnexpectedJsonException;
use Symfony\Component\Debug\Exception\FatalErrorException;

/**
 * @author Artur Doruch <arturdoruch@interia.pl>
 */
class ExceptionProcessor implements EventProcessorInterface
{
    /**
     * @var ExceptionFormatter
     */
    protected $exceptionFormatter;


    public function __construct(ExceptionFormatter $exceptionFormatter = null)
    {
        $this->exceptionFormatter = $exceptionFormatter ?: new ExceptionFormatter(__DIR__ . '/../../../../');
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

        $addExceptionTrace = $options['exception_trace'] ?? null;

        if ($addExceptionTrace === true || $addExceptionTrace === null && $e instanceof FatalErrorException) {
            $exceptionContext['trace'] = $this->exceptionFormatter->getTraceAsHtml($e);
        }

        if (class_exists('\ArturDoruch\Util\Json\UnexpectedJsonException') && $e instanceof UnexpectedJsonException) {
            $exceptionContext['json'] = strlen($json = $e->getJson()) > 5000 ? substr($e->getJson(), 0, 5000) . '...' : $json;
        }

        $context = array_merge(['exception' => $exceptionContext], $context);
        $event->setContext($context);
    }
}
