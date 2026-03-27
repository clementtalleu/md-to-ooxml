<?php

declare(strict_types=1);

namespace Talleu\MdToOoxml\Tests\Unit\Renderer;

use PHPUnit\Framework\TestCase;
use Talleu\MdToOoxml\Node\InlineCodeNode;
use Talleu\MdToOoxml\Renderer\InlineCodeRenderer;

class InlineCodeRendererTest extends TestCase
{
    public function testRenderInlineCode(): void
    {
        $renderer = new InlineCodeRenderer();
        $node = new InlineCodeNode('composer install');
        $xml = $renderer->render($node);

        $this->assertStringContainsString('Courier New', $xml);
        $this->assertStringContainsString('composer install', $xml);
        $this->assertStringContainsString('w:fill="E8E8E8"', $xml);
    }

    public function testXmlEscaping(): void
    {
        $renderer = new InlineCodeRenderer();
        $node = new InlineCodeNode('$a < $b && $c > 0');
        $xml = $renderer->render($node);

        $this->assertStringContainsString('$a &lt; $b &amp;&amp; $c &gt; 0', $xml);
    }
}
