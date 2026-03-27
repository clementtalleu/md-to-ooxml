<?php

declare(strict_types=1);

namespace Talleu\MdToOoxml\Renderer;

use Talleu\MdToOoxml\Node\InlineCodeNode;
use Talleu\MdToOoxml\Node\NodeInterface;

/**
 * Renders inline code with monospace font and a light gray background.
 */
class InlineCodeRenderer implements RendererInterface
{
    public function render(NodeInterface $node): string
    {
        /** @var InlineCodeNode $node */
        $code = htmlspecialchars($node->getCode(), ENT_XML1 | ENT_QUOTES, 'UTF-8');

        return '<w:r>'
            . '<w:rPr>'
            . '<w:rFonts w:ascii="Courier New" w:hAnsi="Courier New" w:cs="Courier New"/>'
            . '<w:shd w:val="clear" w:color="auto" w:fill="E8E8E8"/>'
            . '<w:sz w:val="20"/><w:szCs w:val="20"/>'
            . '</w:rPr>'
            . '<w:t xml:space="preserve">' . $code . '</w:t>'
            . '</w:r>';
    }
}
