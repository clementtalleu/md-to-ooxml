<?php

declare(strict_types=1);

namespace Talleu\MdToOoxml\Renderer;

use Talleu\MdToOoxml\Node\NodeInterface;
use Talleu\MdToOoxml\Node\TextRunNode;

class TextRunRenderer implements RendererInterface
{
    public function render(NodeInterface $node): string
    {
        /** @var TextRunNode $node */
        $rPr = '';

        if ($node->isBold()) {
            $rPr .= '<w:b/>';
        }
        if ($node->isItalic()) {
            $rPr .= '<w:i/>';
        }
        if ($node->isUnderline()) {
            $rPr .= '<w:u w:val="single"/>';
        }
        if ($node->isStrikethrough()) {
            $rPr .= '<w:strike/>';
        }

        $rPrXml = $rPr !== '' ? "<w:rPr>{$rPr}</w:rPr>" : '';
        $text = htmlspecialchars($node->getText(), ENT_XML1 | ENT_QUOTES, 'UTF-8');

        return "<w:r>{$rPrXml}<w:t xml:space=\"preserve\">{$text}</w:t></w:r>";
    }
}
