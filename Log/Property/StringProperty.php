<?php

namespace ArturDoruch\EventLoggerBundle\Log\Property;

/**
 * @author Artur Doruch <arturdoruch@interia.pl>
 */
class StringProperty extends AbstractProperty
{
    /**
     * @var array Truncate options used to cut property displayed on the log list.
     */
    private $truncateOptions = [];

    public function __construct(string $name, string $label)
    {
        parent::__construct($name, $label);
        $this->setTruncateOptions(0);
    }

    public function getType(): string
    {
        return 'string';
    }

    /**
     * @param int $length
     * @param bool $cut Whether to cut the word.
     * @param string $ellipsis
     */
    public function setTruncateOptions(int $length, bool $cut = true, string $ellipsis = '...')
    {
        $this->truncateOptions = [
            'length' => $length,
            'cut' => $cut,
            'ellipsis' => $ellipsis,
        ];
    }

    /**
     * @return array
     *  - length (int)
     *  - cut (bool)
     *  - ellipsis (string)
     */
    public function getTruncateOptions(): array
    {
        return $this->truncateOptions;
    }

    /**
     * Whether the string property displayed on the log list should be truncated.
     *
     * @return bool
     */
    public function truncateRequired(): bool
    {
        return $this->truncateOptions['length'] > 0;
    }
}
