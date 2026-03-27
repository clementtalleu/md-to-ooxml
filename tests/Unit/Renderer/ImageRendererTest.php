<?php

declare(strict_types=1);

namespace Talleu\MdToOoxml\Tests\Unit\Renderer;

use PHPUnit\Framework\TestCase;
use Talleu\MdToOoxml\Node\ImageNode;
use Talleu\MdToOoxml\Renderer\ImageRenderer;

class ImageRendererTest extends TestCase
{
    public function testRenderImage(): void
    {
        $renderer = new ImageRenderer();
        $node = new ImageNode('Company Logo', 'https://example.com/logo.png');
        $xml = $renderer->render($node);

        $this->assertStringContainsString('[Image: Company Logo]', $xml);
        $this->assertStringContainsString('https://example.com/logo.png', $xml);
    }
}
