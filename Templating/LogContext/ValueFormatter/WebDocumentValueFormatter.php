<?php

namespace ArturDoruch\EventLoggerBundle\Templating\LogContext\ValueFormatter;

use ArturDoruch\Json\JsonUtils;
use ArturDoruch\Util\StringUtils;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @author Artur Doruch <arturdoruch@interia.pl>
 */
class WebDocumentValueFormatter extends AbstractValueFormatter
{
    /**
     * The constant marks JSON, XML or HTML code as web document.
     * Add this constant to the beginning of the web document code.
     */
    const DOCUMENT_PREFIX = 'web_document:';

    /**
     * @var string Twig template rendering the web document.
     */
    protected $template = '@ArturDoruchEventLogger/log/context/web_document.html.twig';


    public function setOptions(array $options)
    {
        $optionsResolver = new OptionsResolver();
        $optionsResolver
            ->setDefaults([
                'highlight_json_syntax' => true,
                'highlight_json_syntax_class_prefix' => 'json',
                // The minimum characters, when document content should be initially collapsed.
                'collapse_length' => 200,
                // The regexp detecting XML or HTML code.
                'xml_regexp' => '/<\?xml[^>]+\?>|<(html|iframe|script|style)[^>]*>.*<\/(\1)>|<(link|meta|img|input|embed) [^>]*\/?>/si',
            ])
            ->setAllowedTypes('highlight_json_syntax', 'boolean')
            ->setAllowedTypes('highlight_json_syntax_class_prefix', 'string')
            ->setAllowedTypes('collapse_length', 'integer')
            ->setAllowedTypes('xml_regexp', 'string');

        $this->options = $optionsResolver->resolve($options);
    }


    public function supports(string $name, $value): bool
    {
        if (!is_string($value)) {
            return false;
        }

        if (!$value = trim($value)) {
            return false;
        }

        if (StringUtils::startsWith(self::DOCUMENT_PREFIX, $value)) {
            return true;
        }

        if ($this->decodeIfJson($value) !== null) {
            return true;
        }

        if (preg_match($this->options['xml_regexp'], $value)) {
            return true;
        }

        return false;
    }


    public function format(string $name, $value)
    {
        if (!$value = preg_replace('/^' . self::DOCUMENT_PREFIX . '/', '', $value)) {
            return '';
        }

        if ($jsonData = $this->decodeIfJson($value)) {
            $value = json_encode($jsonData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

            if ($this->options['highlight_json_syntax']) {
                $value = JsonUtils::highlightSyntax($value, $this->options['highlight_json_syntax_class_prefix']);
            }
        }

        return $this->twig->render($this->template, [
            'index' => mt_rand(),
            'document' => $value,
            'contentType' => $jsonData ? 'json' : 'xml',
            'highlight' => $this->options['highlight_json_syntax'],
            'collapse' => strlen($value) > $this->options['collapse_length']
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
