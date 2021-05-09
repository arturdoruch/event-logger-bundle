<?php

namespace ArturDoruch\EventLoggerBundle\EventProcessor;

use ArturDoruch\EventLoggerBundle\Event;
use ArturDoruch\ExceptionFormatter\Exception\FormattedException;
use ArturDoruch\ExceptionFormatter\ExceptionFormatter;
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

    private $options = [
        'shorten_filename' => true,
        'argument_max_length' => 1000,
        'templates' => []
    ];

    /**
     * @var array
     */
    private $processOptions;

    /**
     * @param array $options Exception trace formatting options.
     *  - shorten_filename (bool) default: true
     *  - argument_max_length (int) default: 1000
     *  - templates (array)
     */
    public function __construct(array $options = [])
    {
        $options += $this->options;
        $this->processOptions = [
            'exception_trace_argument_max_length' => $options['argument_max_length'],
            'exception_trace' => null,
        ];

        if ($options['shorten_filename'] === true) {
            $options['file_base_dir'] = __DIR__ . '/../../../../';
        }

        $this->exceptionFormatter = new ExceptionFormatter($options, $options['templates']);
    }

    /**
     * {@inheritdoc}
     *
     * @param array $options
     *
     *  - exception_trace (bool|null) default: null
     *    Whether to add exception trace to the context. If null trace will be added only for the
     *    Symfony\Component\Debug\Exception\FatalErrorException.
     *
     *  - exception_trace_argument_max_length (int) default: Value specified in __construct() "argument_max_length" option.
     *    Maximum length of the function argument with type of string in exception trace entry.
     *    The longer string will be truncated. If 0 not limit will be used.
     */
    public function process(string $level, Event $event, array &$options)
    {
        if (!$e = $event->getThrowable()) {
            return;
        }

        $options += $this->processOptions;
        $this->exceptionFormatter->setArgumentMaxLength($options['exception_trace_argument_max_length']);
        $formattedException = $this->exceptionFormatter->format($e);

        $exceptionContext = $this->createContext($formattedException, $options['exception_trace'], false);
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
