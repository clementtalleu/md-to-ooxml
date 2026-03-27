<?php

declare(strict_types=1);

namespace Talleu\MdToOoxml\Renderer;

use Talleu\MdToOoxml\Node\NodeInterface;
use Talleu\MdToOoxml\Node\TitleNode;

/**
 * Renders headings using proper Word heading styles (Heading1 through Heading6).
 */
class TitleRenderer implements RendererInterface
{
    /** Font sizes in half-points for each heading level. */
    private const HEADING_SIZES = [
        1 => 48, // 24pt
        2 => 36, // 18pt
        3 => 28, // 14pt
        4 => 24, // 12pt
        5 => 22, // 11pt
        6 => 20, // 10pt
    ];

    public function __construct(
        private readonly NodeRenderer $nodeRenderer,
    ) {}

    public function render(NodeInterface $node): string
    {
        /** @var TitleNode $node */
        $level = $node->getLevel();
        $size = self::HEADING_SIZES[$level] ?? 24;

        $childrenXml = '';
        foreach ($node->getChildren() as $child) {
            $childrenXml .= $this->nodeRenderer->render($child);
        }

        return '<w:p>'
            . '<w:pPr>'
            . '<w:pStyle w:val="Heading' . $level . '"/>'
            . '<w:spacing w:before="240" w:after="120"/>'
            . '<w:rPr><w:sz w:val="' . $size . '"/><w:szCs w:val="' . $size . '"/></w:rPr>'
            . '</w:pPr>'
            . $childrenXml
            . '</w:p>';
    }
}
