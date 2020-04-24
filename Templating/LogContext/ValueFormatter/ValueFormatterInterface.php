<?php

namespace ArturDoruch\EventLoggerBundle\Templating\LogContext\ValueFormatter;

use Twig\Environment;

/**
 * @author Artur Doruch <arturdoruch@interia.pl>
 */
interface ValueFormatterInterface
{
    /**
     * @param Environment $twig
     */
    public function setTwig(Environment $twig);

    /**
     * Sets Twig template name displaying the formatted value.
     *
     * @param string $template
     */
    public function setTemplate(string $template);

    /**
     * Checks if formatter supports specific log context value.
     *
     * @param string $name The context name.
     * @param mixed $value The context array or scalar value.
     *
     * @return bool
     */
    public function supports(string $name, $value): bool;

    /**
     * Formats the log context array or scalar value.
     *
     * @param string $name The context name.
     * @param mixed $value The context array or scalar value.
     *
     * @return mixed The formatted value (array or scalar).
     */
    public function format(string $name, $value);
}
