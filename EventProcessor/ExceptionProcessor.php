<?php

namespace ArturDoruch\EventLoggerBundle\EventProcessor;

use ArturDoruch\EventLoggerBundle\Event;
use ArturDoruch\ExceptionFormatter\ExceptionFormatter;
use ArturDoruch\ExceptionFormatter\Exception\FormattedException;
use ArturDoruch\Json\UnexpectedJsonException;
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

        $formattedException = $this->exceptionFormatter->format($e);
        $exceptionContext = $this->createContext($formattedException, $options['exception_trace'] ?? null, false);
        $context = $event->getContext();

        if (isset($context['exception'])) {
            $context['exception'] = array_merge($context['exception'], $exceptionContext);
        } else {
            $context = array_merge(['exception' => $exceptionContext], $context);
        }

        $event->setContext($context);
    }


    private function createContext(FormattedException $exception, ?bool $addTrace, bool $addMessage = true)
    {
        if ($addMessage) {
            $context['message'] = $addMessage;
        }

        $context['class'] = $exception->getClass();
        $context['file'] = $exception->getFile() . ':' . $exception->getLine();

        $original = $exception->getOriginal();

        if ($addTrace === true || $addTrace === null && $original instanceof FatalErrorException) {
            $context['trace'] = $exception->getTraceAsString();
        }

        if ($original instanceof UnexpectedJsonException) {
            $json = $original->getJson();
            $context['json'] = strlen($json) > 5000 ? mb_substr($json, 0, 5000) . '...' : $json;
        }

        if ($exception->getPrevious()) {
            $context['previous'] = $this->createContext($exception->getPrevious(), $addTrace);
        }

        return $context;
    }
}
