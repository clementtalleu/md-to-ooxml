<?php

declare(strict_types=1);

namespace Talleu\MdToOoxml\Tests\Unit\Renderer;

use PHPUnit\Framework\TestCase;
use Talleu\MdToOoxml\Node\ParagraphNode;
use Talleu\MdToOoxml\Node\TextRunNode;
use Talleu\MdToOoxml\Renderer\NodeRenderer;
use Talleu\MdToOoxml\Renderer\ParagraphRenderer;
use Talleu\MdToOoxml\Renderer\TextRunRenderer;

class ParagraphRendererTest extends TestCase
{
    public function testRenderParagraphWithChildren(): void
    {
        $nodeRenderer = new NodeRenderer();
        $nodeRenderer->addRenderer(TextRunNode::class, new TextRunRenderer());
        $nodeRenderer->addRenderer(ParagraphNode::class, new ParagraphRenderer($nodeRenderer));

        $paragraph = new ParagraphNode();
        $paragraph->addChild(new TextRunNode('Hello '));
        $paragraph->addChild(new TextRunNode('world', isBold: true));

        $xml = $nodeRenderer->render($paragraph);

        $this->assertStringStartsWith('<w:p>', $xml);
        $this->assertStringEndsWith('</w:p>', $xml);
        $this->assertStringContainsString('Hello ', $xml);
        $this->assertStringContainsString('<w:b/>', $xml);
    }

    public function testEmptyParagraph(): void
    {
        $nodeRenderer = new NodeRenderer();
        $nodeRenderer->addRenderer(ParagraphNode::class, new ParagraphRenderer($nodeRenderer));

        $paragraph = new ParagraphNode();
        $xml = $nodeRenderer->render($paragraph);

        $this->assertSame('<w:p></w:p>', $xml);
    }
}
