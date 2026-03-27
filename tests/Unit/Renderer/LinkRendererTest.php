<?php

declare(strict_types=1);

namespace Talleu\MdToOoxml\Tests\Unit\Renderer;

use PHPUnit\Framework\TestCase;
use Talleu\MdToOoxml\Node\LinkNode;
use Talleu\MdToOoxml\Renderer\LinkRenderer;

class LinkRendererTest extends TestCase
{
    private LinkRenderer $renderer;

    protected function setUp(): void
    {
        $this->renderer = new LinkRenderer();
    }

    public function testRenderLink(): void
    {
        $node = new LinkNode('Google', 'https://google.com');
        $xml = $this->renderer->render($node);

        $this->assertStringContainsString('HYPERLINK', $xml);
        $this->assertStringContainsString('https://google.com', $xml);
        $this->assertStringContainsString('Google', $xml);
        $this->assertStringContainsString('<w:u w:val="single"/>', $xml);
        $this->assertStringContainsString('<w:color w:val="0563C1"/>', $xml);
    }

    public function testUrlEscaping(): void
    {
        $node = new LinkNode('Test', 'https://example.com/?a=1&b=2');
        $xml = $this->renderer->render($node);

        $this->assertStringContainsString('a=1&amp;b=2', $xml);
    }
}
