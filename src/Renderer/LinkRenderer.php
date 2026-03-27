<?php

declare(strict_types=1);

namespace Talleu\MdToOoxml\Renderer;

use Talleu\MdToOoxml\Node\LinkNode;
use Talleu\MdToOoxml\Node\NodeInterface;

/**
 * Renders links using Word field codes (HYPERLINK).
 * This approach avoids the need to manage relationship files (.rels).
 */
class LinkRenderer implements RendererInterface
{
    public function render(NodeInterface $node): string
    {
        /** @var LinkNode $node */
        $url = htmlspecialchars($node->getUrl(), ENT_XML1 | ENT_QUOTES, 'UTF-8');
        $text = htmlspecialchars($node->getText(), ENT_XML1 | ENT_QUOTES, 'UTF-8');

        return '<w:r><w:fldChar w:fldCharType="begin"/></w:r>'
            . '<w:r><w:instrText xml:space="preserve"> HYPERLINK "' . $url . '" </w:instrText></w:r>'
            . '<w:r><w:fldChar w:fldCharType="separate"/></w:r>'
            . '<w:r><w:rPr><w:color w:val="0563C1"/><w:u w:val="single"/></w:rPr>'
            . '<w:t xml:space="preserve">' . $text . '</w:t></w:r>'
            . '<w:r><w:fldChar w:fldCharType="end"/></w:r>';
    }
}
