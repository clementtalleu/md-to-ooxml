<?php

declare(strict_types=1);

namespace Talleu\MdToOoxml\Renderer;

use Talleu\MdToOoxml\Node\NodeInterface;
use Talleu\MdToOoxml\Node\QuoteNode;

class QuoteRenderer implements RendererInterface
{
    public function __construct(
        private readonly NodeRenderer $nodeRenderer,
    ) {}

    public function render(NodeInterface $node): string
    {
        /** @var QuoteNode $node */
        $childrenXml = '';

        foreach ($node->getChildren() as $child) {
            $childrenXml .= $this->nodeRenderer->render($child);
        }

        // Left indent of 720 twips (~1.27cm), with a left border for blockquote visual
        return '<w:p>'
            . '<w:pPr>'
            . '<w:pBdr><w:left w:val="single" w:sz="12" w:space="4" w:color="CCCCCC"/></w:pBdr>'
            . '<w:ind w:left="720"/>'
            . '<w:rPr><w:color w:val="666666"/></w:rPr>'
            . '</w:pPr>'
            . $childrenXml
            . '</w:p>';
    }
}
