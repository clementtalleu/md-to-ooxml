<?php

declare(strict_types=1);

namespace Talleu\MdToOoxml;

use Talleu\MdToOoxml\Parser\MarkdownParserInterface;
use Talleu\MdToOoxml\Renderer\NodeRenderer;

/**
 * Main converter: takes Markdown input and produces OOXML output.
 *
 * Use OoXmlConverterFactory::create() for a ready-to-use instance,
 * or inject your own parser/renderer for custom configurations.
 */
class OoXmlConverter
{
    public function __construct(
        private readonly MarkdownParserInterface $parser,
        private readonly NodeRenderer $renderer,
    ) {}

    /**
     * Convert a Markdown string to a full OOXML document string.
     *
     * The output is a complete document.xml content including
     * the XML declaration and <w:document> envelope.
     */
    public function convert(string $markdown): string
    {
        $documentNode = $this->parser->parse($markdown);

        return $this->renderer->render($documentNode);
    }

    /**
     * Convert Markdown to OOXML body content only (without the document envelope).
     *
     * Useful when you need to inject OOXML fragments into an existing document.
     */
    public function convertToBodyXml(string $markdown): string
    {
        $documentNode = $this->parser->parse($markdown);
        $bodyXml = '';

        foreach ($documentNode->getChildren() as $child) {
            $bodyXml .= $this->renderer->render($child);
        }

        return $bodyXml;
    }
}
