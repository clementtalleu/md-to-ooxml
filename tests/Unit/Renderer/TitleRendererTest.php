<?php

declare(strict_types=1);

namespace Talleu\MdToOoxml\Tests\Unit\Renderer;

use PHPUnit\Framework\TestCase;
use Talleu\MdToOoxml\Node\TextRunNode;
use Talleu\MdToOoxml\Node\TitleNode;
use Talleu\MdToOoxml\Renderer\NodeRenderer;
use Talleu\MdToOoxml\Renderer\TextRunRenderer;
use Talleu\MdToOoxml\Renderer\TitleRenderer;

class TitleRendererTest extends TestCase
{
    private NodeRenderer $nodeRenderer;

    protected function setUp(): void
    {
        $this->nodeRenderer = new NodeRenderer();
        $this->nodeRenderer->addRenderer(TextRunNode::class, new TextRunRenderer());
        $this->nodeRenderer->addRenderer(TitleNode::class, new TitleRenderer($this->nodeRenderer));
    }

    public function testHeading1(): void
    {
        $title = new TitleNode(1);
        $title->addChild(new TextRunNode('Main Title'));

        $xml = $this->nodeRenderer->render($title);

        $this->assertStringContainsString('<w:pStyle w:val="Heading1"/>', $xml);
        $this->assertStringContainsString('<w:sz w:val="48"/>', $xml);
        $this->assertStringContainsString('Main Title', $xml);
    }

    public function testHeading3(): void
    {
        $title = new TitleNode(3);
        $title->addChild(new TextRunNode('Sub Title'));

        $xml = $this->nodeRenderer->render($title);

        $this->assertStringContainsString('<w:pStyle w:val="Heading3"/>', $xml);
        $this->assertStringContainsString('<w:sz w:val="28"/>', $xml);
    }

    public function testAllHeadingLevelsHaveStyles(): void
    {
        for ($level = 1; $level <= 6; $level++) {
            $title = new TitleNode($level);
            $title->addChild(new TextRunNode("H{$level}"));

            $xml = $this->nodeRenderer->render($title);
            $this->assertStringContainsString("<w:pStyle w:val=\"Heading{$level}\"/>", $xml);
        }
    }
}
