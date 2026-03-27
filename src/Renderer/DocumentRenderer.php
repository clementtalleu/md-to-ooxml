<?php

declare(strict_types=1);

namespace Talleu\MdToOoxml\Renderer;

use Talleu\MdToOoxml\Node\DocumentNode;
use Talleu\MdToOoxml\Node\NodeInterface;

class DocumentRenderer implements RendererInterface
{
    public function __construct(
        private readonly NodeRenderer $nodeRenderer,
    ) {}

    public function render(NodeInterface $node): string
    {
        /** @var DocumentNode $node */
        $bodyXml = '';

        foreach ($node->getChildren() as $child) {
            $bodyXml .= $this->nodeRenderer->render($child);
        }

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<w:document xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main"'
            . ' xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            . '<w:body>'
            . $bodyXml
            . '</w:body>'
            . '</w:document>';
    }
}
