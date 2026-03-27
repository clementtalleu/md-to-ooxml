<?php

declare(strict_types=1);

namespace Talleu\MdToOoxml\Renderer;

use Talleu\MdToOoxml\Node\NodeInterface;

/**
 * Renders a horizontal rule as an empty paragraph with a bottom border.
 */
class HorizontalRuleRenderer implements RendererInterface
{
    public function render(NodeInterface $node): string
    {
        return '<w:p>'
            . '<w:pPr>'
            . '<w:pBdr><w:bottom w:val="single" w:sz="6" w:space="1" w:color="999999"/></w:pBdr>'
            . '<w:spacing w:before="120" w:after="120"/>'
            . '</w:pPr>'
            . '</w:p>';
    }
}
