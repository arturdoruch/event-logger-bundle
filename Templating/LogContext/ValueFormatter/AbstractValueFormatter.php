<?php

namespace ArturDoruch\EventLoggerBundle\Templating\LogContext\ValueFormatter;

use Twig\Environment;

/**
 * @author Artur Doruch <arturdoruch@interia.pl>
 */
abstract class AbstractValueFormatter implements ValueFormatterInterface
{
    /**
     * @var Environment;
     */
    protected $twig;

    /**
     * @var string
     */
    protected $template;

    /**
     * @var array
     */
    protected $options = [];

    /**
     * @inheritdoc
     */
    public function setTwig(Environment $twig)
    {
        $this->twig = $twig;
    }

    /**
     * @inheritdoc
     */
    public function setTemplate(string $template)
    {
        $this->template = $template;
    }

    /**
     * @inheritdoc
     */
    public function setOptions(array $options)
    {
        $this->options = $options;
    }
}
