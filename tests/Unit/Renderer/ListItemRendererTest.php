<?php

declare(strict_types=1);

namespace Talleu\MdToOoxml\Tests\Unit\Renderer;

use PHPUnit\Framework\TestCase;
use Talleu\MdToOoxml\Node\ListItemNode;
use Talleu\MdToOoxml\Node\TextRunNode;
use Talleu\MdToOoxml\Renderer\ListItemRenderer;
use Talleu\MdToOoxml\Renderer\NodeRenderer;
use Talleu\MdToOoxml\Renderer\TextRunRenderer;

class ListItemRendererTest extends TestCase
{
    private NodeRenderer $nodeRenderer;

    protected function setUp(): void
    {
        $this->nodeRenderer = new NodeRenderer();
        $this->nodeRenderer->addRenderer(TextRunNode::class, new TextRunRenderer());
        $this->nodeRenderer->addRenderer(ListItemNode::class, new ListItemRenderer($this->nodeRenderer));
    }

    public function testBulletListItem(): void
    {
        $item = new ListItemNode('bullet');
        $item->addChild(new TextRunNode('Bullet item'));

        $xml = $this->nodeRenderer->render($item);

        $this->assertStringContainsString('<w:numId w:val="1"/>', $xml);
        $this->assertStringContainsString('<w:ilvl w:val="0"/>', $xml);
        $this->assertStringContainsString('Bullet item', $xml);
    }

    public function testOrderedListItem(): void
    {
        $item = new ListItemNode('number');
        $item->addChild(new TextRunNode('Numbered item'));

        $xml = $this->nodeRenderer->render($item);

        $this->assertStringContainsString('<w:numId w:val="2"/>', $xml);
        $this->assertStringContainsString('Numbered item', $xml);
    }
}
