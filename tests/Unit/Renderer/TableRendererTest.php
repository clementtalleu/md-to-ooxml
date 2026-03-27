<?php

declare(strict_types=1);

namespace Talleu\MdToOoxml\Tests\Unit\Renderer;

use PHPUnit\Framework\TestCase;
use Talleu\MdToOoxml\Node\TableCellNode;
use Talleu\MdToOoxml\Node\TableNode;
use Talleu\MdToOoxml\Node\TableRowNode;
use Talleu\MdToOoxml\Node\TextRunNode;
use Talleu\MdToOoxml\Renderer\NodeRenderer;
use Talleu\MdToOoxml\Renderer\TableRenderer;
use Talleu\MdToOoxml\Renderer\TextRunRenderer;

class TableRendererTest extends TestCase
{
    private NodeRenderer $nodeRenderer;

    protected function setUp(): void
    {
        $this->nodeRenderer = new NodeRenderer();
        $this->nodeRenderer->addRenderer(TextRunNode::class, new TextRunRenderer());
        $this->nodeRenderer->addRenderer(TableNode::class, new TableRenderer($this->nodeRenderer));
    }

    public function testRenderTable(): void
    {
        $table = new TableNode();

        // Header row
        $header = new TableRowNode(isHeader: true);
        $cell1 = new TableCellNode();
        $cell1->addChild(new TextRunNode('Name'));
        $cell2 = new TableCellNode();
        $cell2->addChild(new TextRunNode('Age'));
        $header->addCell($cell1);
        $header->addCell($cell2);
        $table->addRow($header);

        // Data row
        $row = new TableRowNode();
        $dataCell1 = new TableCellNode();
        $dataCell1->addChild(new TextRunNode('Alice'));
        $dataCell2 = new TableCellNode();
        $dataCell2->addChild(new TextRunNode('30'));
        $row->addCell($dataCell1);
        $row->addCell($dataCell2);
        $table->addRow($row);

        $xml = $this->nodeRenderer->render($table);

        $this->assertStringContainsString('<w:tbl>', $xml);
        $this->assertStringContainsString('</w:tbl>', $xml);
        $this->assertStringContainsString('<w:tr>', $xml);
        $this->assertStringContainsString('<w:tc>', $xml);
        $this->assertStringContainsString('Name', $xml);
        $this->assertStringContainsString('Alice', $xml);
        // Header row should have shading
        $this->assertStringContainsString('w:fill="D9E2F3"', $xml);
    }
}
