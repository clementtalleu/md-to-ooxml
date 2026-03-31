<?php

declare(strict_types=1);

namespace Talleu\MdToOoxml\Tests\Unit\Renderer;

use PHPUnit\Framework\TestCase;
use Talleu\MdToOoxml\Node\ImageNode;
use Talleu\MdToOoxml\Renderer\ImageRenderer;

class ImageRendererTest extends TestCase
{
    public function testRenderReturnsRunWithoutParagraphWrapper(): void
    {
        $node = new ImageNode('Logo du cabinet', 'https://example.com/logo.png');
        $renderer = new ImageRenderer();
        $result = $renderer->render($node);

        // Must return a <w:r>, not a <w:p>
        $this->assertStringStartsWith('<w:r>', $result);
        $this->assertStringEndsWith('</w:r>', $result);
        $this->assertStringNotContainsString('<w:p>', $result);

        // Content check
        $this->assertStringContainsString('[Image: Logo du cabinet]', $result);
        $this->assertStringContainsString('https://example.com/logo.png', $result);
    }
}
