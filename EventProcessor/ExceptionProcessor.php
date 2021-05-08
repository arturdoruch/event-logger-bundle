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
    private $exceptionFormatter;

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


    protected function createContext(FormattedException $exception, ?bool $addTrace, bool $addMessage = true): array
    {
        if ($addMessage) {
            $context['message'] = $exception->getMessage();
        }

        $context['class'] = $exception->getClass();
        $context['file'] = $exception->getFile() . ':' . $exception->getLine();

        $original = $exception->getOriginal();

        if ($addTrace === true || $addTrace === null && $original instanceof FatalErrorException) {
            $context['trace'] = $exception->getTraceAsString();
        }

        $this->addExtraContext($context, $original);

        if ($exception->getPrevious()) {
            $context['previous'] = $this->createContext($exception->getPrevious(), $addTrace);
        }

        return $context;
    }

    /**
     * Adds extra context getting from the exception class.
     *
     * @param array $context
     * @param \Throwable $exception
     */
    protected function addExtraContext(array &$context, \Throwable $exception)
    {
        if ($exception instanceof UnexpectedJsonException) {
            $json = $exception->getJson();
            $context['json'] = strlen($json) > 5000 ? mb_substr($json, 0, 5000) . '...' : $json;
        }
    }
}
