<?php

declare(strict_types=1);

namespace Talleu\MdToOoxml\Renderer;

use Talleu\MdToOoxml\Node\NodeInterface;
use Talleu\MdToOoxml\Node\ParagraphNode;

class ParagraphRenderer implements RendererInterface
{
    public function __construct(
        private readonly NodeRenderer $nodeRenderer,
    ) {}

    public function render(NodeInterface $node): string
    {
        /** @var ParagraphNode $node */
        $childrenXml = '';

        foreach ($node->getChildren() as $child) {
            $childrenXml .= $this->nodeRenderer->render($child);
        }

        return "<w:p>{$childrenXml}</w:p>";
    }
}
