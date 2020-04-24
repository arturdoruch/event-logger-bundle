<?php

namespace ArturDoruch\EventLoggerBundle\Twig\Extension;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

/**
 * @author Artur Doruch <arturdoruch@interia.pl>
 */
class MiscExtension extends AbstractExtension
{
    /**
     * @var string
     */
    private $baseTemplate;


    public function __construct(string $baseTemplate)
    {
        $this->baseTemplate = $baseTemplate;
    }


    public function getFilters()
    {
        return [
            new TwigFilter('arturdoruch_eventlogger_format_date', [$this, 'formatDate']),
        ];
    }


    public function getFunctions()
    {
        return [
            new TwigFunction('arturdoruch_eventlogger_base_template', function () {
                return $this->baseTemplate;
            }),
        ];
    }

    /**
     * @param \DateTime $date
     * @param string $format
     *
     * @return null|string
     */
    public function formatDate($date, string $format): ?string
    {
        if (!$date instanceof \DateTime) {
            return null;
        }

        return $date->format($format);
    }
}
 