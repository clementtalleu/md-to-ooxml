<?php

declare(strict_types=1);

namespace Talleu\MdToOoxml\Renderer;

use Talleu\MdToOoxml\Node\NodeInterface;
use Talleu\MdToOoxml\Node\TableNode;

/**
 * Renders a table as a proper OOXML <w:tbl> element with borders.
 */
class TableRenderer implements RendererInterface
{
    public function __construct(
        private readonly NodeRenderer $nodeRenderer,
    ) {}

    public function render(NodeInterface $node): string
    {
        /** @var TableNode $node */
        $xml = '<w:tbl>';

        // Table properties: full-width with single borders
        $xml .= '<w:tblPr>'
            . '<w:tblW w:w="5000" w:type="pct"/>'
            . '<w:tblBorders>'
            . '<w:top w:val="single" w:sz="4" w:space="0" w:color="000000"/>'
            . '<w:left w:val="single" w:sz="4" w:space="0" w:color="000000"/>'
            . '<w:bottom w:val="single" w:sz="4" w:space="0" w:color="000000"/>'
            . '<w:right w:val="single" w:sz="4" w:space="0" w:color="000000"/>'
            . '<w:insideH w:val="single" w:sz="4" w:space="0" w:color="000000"/>'
            . '<w:insideV w:val="single" w:sz="4" w:space="0" w:color="000000"/>'
            . '</w:tblBorders>'
            . '</w:tblPr>';

        foreach ($node->getRows() as $row) {
            $xml .= '<w:tr>';

            foreach ($row->getCells() as $cell) {
                $xml .= '<w:tc>';

                // Bold text in header cells
                $cellContent = '';
                foreach ($cell->getChildren() as $child) {
                    $cellContent .= $this->nodeRenderer->render($child);
                }

                if ($row->isHeader()) {
                    $xml .= '<w:tcPr><w:shd w:val="clear" w:color="auto" w:fill="D9E2F3"/></w:tcPr>';
                }

                $xml .= '<w:p>';
                if ($row->isHeader()) {
                    $xml .= '<w:pPr><w:rPr><w:b/></w:rPr></w:pPr>';
                }
                $xml .= $cellContent;
                $xml .= '</w:p>';

                $xml .= '</w:tc>';
            }

            $xml .= '</w:tr>';
        }

        $xml .= '</w:tbl>';

        return $xml;
    }
}
