<?php

declare(strict_types=1);

namespace Talleu\MdToOoxml\Renderer;

use Talleu\MdToOoxml\Node\ImageNode;
use Talleu\MdToOoxml\Node\NodeInterface;

/**
 * Renders images as a placeholder paragraph with the alt text and URL.
 *
 * Embedding actual binary images in OOXML requires relationship management
 * and base64 encoding. This renderer outputs a visible reference that can
 * be post-processed or is useful as a text-only representation.
 */
class ImageRenderer implements RendererInterface
{
    public function render(NodeInterface $node): string
    {
        /** @var ImageNode $node */
        $alt = htmlspecialchars($node->getAltText(), ENT_XML1 | ENT_QUOTES, 'UTF-8');
        $url = htmlspecialchars($node->getUrl(), ENT_XML1 | ENT_QUOTES, 'UTF-8');

        return '<w:p>'
            . '<w:r><w:rPr><w:i/><w:color w:val="666666"/></w:rPr>'
            . '<w:t xml:space="preserve">[Image: ' . $alt . '] (' . $url . ')</w:t></w:r>'
            . '</w:p>';
    }
}
