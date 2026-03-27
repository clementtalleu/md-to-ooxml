<?php

declare(strict_types=1);

namespace Talleu\MdToOoxml\Renderer;

use Talleu\MdToOoxml\Node\ListItemNode;
use Talleu\MdToOoxml\Node\NodeInterface;

class ListItemRenderer implements RendererInterface
{
    public function __construct(
        private readonly NodeRenderer $nodeRenderer,
    ) {}

    public function render(NodeInterface $node): string
    {
        /** @var ListItemNode $node */
        $childrenXml = '';

        foreach ($node->getChildren() as $child) {
            $childrenXml .= $this->nodeRenderer->render($child);
        }

        $numId = $node->getListType() === 'number' ? '2' : '1';

        return '<w:p>'
            . '<w:pPr>'
            . '<w:numPr>'
            . '<w:ilvl w:val="0"/>'
            . '<w:numId w:val="' . $numId . '"/>'
            . '</w:numPr>'
            . '</w:pPr>'
            . $childrenXml
            . '</w:p>';
    }
}
