<?php

namespace ArturDoruch\EventLoggerBundle\Templating\LogContext\ValueFormatter;

use Twig\Environment;

/**
 * @author Artur Doruch <arturdoruch@interia.pl>
 */
class ValueFormatterManager
{
    /**
     * @var ValueFormatterInterface[]
     */
    private $formatters = [];

    /**
     * @var Environment
     */
    private $twig;

    public function __construct(Environment $twig)
    {
        $this->twig = $twig;
    }

    public function add(ValueFormatterInterface $formatter)
    {
        $formatter->setTwig($this->twig);
        $this->formatters[] = $formatter;
    }

    /**
     * Formats the log context array or scalar value.
     *
     * @param string $name The context name.
     * @param mixed $value The context array or scalar value.
     *
     * @return mixed The formatted value (array or scalar).
     */
    public function format(string $name, $value)
    {
        foreach ($this->formatters as $formatter) {
            if ($formatter->supports($name, $value)) {
                $value = $formatter->format($name, $value);
            }
        }

        return $value;
    }
}
