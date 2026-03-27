<?php

declare(strict_types=1);

namespace Talleu\MdToOoxml\Renderer;

use Talleu\MdToOoxml\Node\CodeBlockNode;
use Talleu\MdToOoxml\Node\NodeInterface;

/**
 * Renders fenced code blocks with monospace font, gray background and borders.
 * Multi-line code is split into separate <w:p> elements sharing the same style,
 * which is the correct way to handle line breaks in OOXML.
 */
class CodeBlockRenderer implements RendererInterface
{
    public function render(NodeInterface $node): string
    {
        /** @var CodeBlockNode $node */
        $lines = explode("\n", $node->getCode());
        $xml = '';

        foreach ($lines as $line) {
            $escaped = htmlspecialchars($line, ENT_XML1 | ENT_QUOTES, 'UTF-8');

            $xml .= '<w:p>'
                . '<w:pPr>'
                . '<w:pBdr>'
                . '<w:top w:val="single" w:sz="4" w:space="1" w:color="D3D3D3"/>'
                . '<w:left w:val="single" w:sz="4" w:space="4" w:color="D3D3D3"/>'
                . '<w:bottom w:val="single" w:sz="4" w:space="1" w:color="D3D3D3"/>'
                . '<w:right w:val="single" w:sz="4" w:space="4" w:color="D3D3D3"/>'
                . '</w:pBdr>'
                . '<w:shd w:val="clear" w:color="auto" w:fill="F5F5F5"/>'
                . '<w:spacing w:after="0" w:line="240" w:lineRule="auto"/>'
                . '</w:pPr>'
                . '<w:r>'
                . '<w:rPr><w:rFonts w:ascii="Courier New" w:hAnsi="Courier New" w:cs="Courier New"/><w:sz w:val="20"/><w:szCs w:val="20"/></w:rPr>'
                . '<w:t xml:space="preserve">' . $escaped . '</w:t>'
                . '</w:r>'
                . '</w:p>';
        }

        return $xml;
    }
}
