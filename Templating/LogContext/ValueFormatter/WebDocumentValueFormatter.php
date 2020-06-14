<?php

namespace ArturDoruch\EventLoggerBundle\Templating\LogContext\ValueFormatter;

use ArturDoruch\Util\StringUtils;

/**
 * @author Artur Doruch <arturdoruch@interia.pl>
 */
class WebDocumentValueFormatter extends AbstractValueFormatter
{
    /**
     * Add this constant to the beginning of the web content string, to marks string as web content.
     */
    const VALUE_PREFIX = 'web_document:';

    /**
     * @var string Twig template rendering the web document.
     */
    protected $template = '@ArturDoruchEventLogger/log/context/web_document.html.twig';


    public function supports(string $name, $value): bool
    {
        if (!$value || !is_string($value)) {
            return false;
        }

        if (StringUtils::startsWith(self::VALUE_PREFIX, $value)) {
            return true;
        }

        if ($this->decodeIfJson($value) !== null) {
            return true;
        }

        if (preg_match('/\<html.+\<\/html\>$/i', trim($value))) {
            return true;
        }

        /*if (preg_match('/\<([a-z]{2,})[^\>]*\>.+\<\/\1\>$/i', $value)) {
            return true;
        }*/

        return false;
    }


    public function format(string $name, $value)
    {
        if (!$value = preg_replace('/^' . self::VALUE_PREFIX . '/', '', $value)) {
            return '';
        }

        if ($array = $this->decodeIfJson($value)) {
            $value = json_encode($array, JSON_PRETTY_PRINT);
        }

        return $this->twig->render($this->template, [
            'index' => mt_rand(),
            'document' => $value,
        ]);
    }

    /**
     * @param mixed $value
     *
     * @return array|null
     */
    private function decodeIfJson($value): ?array
    {
        if (($array = json_decode($value, true)) !== null && $array != $value) {
            return $array;
        }

        return null;
    }
}
