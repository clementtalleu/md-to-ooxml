<?php

declare(strict_types=1);

namespace Talleu\MdToOoxml\Tests\Unit\Renderer;

use PHPUnit\Framework\TestCase;
use Talleu\MdToOoxml\Node\QuoteNode;
use Talleu\MdToOoxml\Node\TextRunNode;
use Talleu\MdToOoxml\Renderer\NodeRenderer;
use Talleu\MdToOoxml\Renderer\QuoteRenderer;
use Talleu\MdToOoxml\Renderer\TextRunRenderer;

class QuoteRendererTest extends TestCase
{
    public function testRenderBlockquote(): void
    {
        $nodeRenderer = new NodeRenderer();
        $nodeRenderer->addRenderer(TextRunNode::class, new TextRunRenderer());
        $nodeRenderer->addRenderer(QuoteNode::class, new QuoteRenderer($nodeRenderer));

        $quote = new QuoteNode();
        $quote->addChild(new TextRunNode('A wise quote'));

        $xml = $nodeRenderer->render($quote);

        $this->assertStringContainsString('<w:ind w:left="720"/>', $xml);
        $this->assertStringContainsString('A wise quote', $xml);
        $this->assertStringContainsString('<w:pBdr>', $xml);
    }
}
