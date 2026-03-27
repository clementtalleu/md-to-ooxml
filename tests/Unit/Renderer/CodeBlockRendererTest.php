<?php

declare(strict_types=1);

namespace Talleu\MdToOoxml\Tests\Unit\Renderer;

use PHPUnit\Framework\TestCase;
use Talleu\MdToOoxml\Node\CodeBlockNode;
use Talleu\MdToOoxml\Renderer\CodeBlockRenderer;

class CodeBlockRendererTest extends TestCase
{
    private CodeBlockRenderer $renderer;

    protected function setUp(): void
    {
        $this->renderer = new CodeBlockRenderer();
    }

    public function testSingleLineCode(): void
    {
        $node = new CodeBlockNode('echo "hello";');
        $xml = $this->renderer->render($node);

        $this->assertStringContainsString('Courier New', $xml);
        $this->assertStringContainsString('echo &quot;hello&quot;;', $xml);
        $this->assertStringContainsString('w:fill="F5F5F5"', $xml);
    }

    public function testMultiLineCodeProducesMultipleParagraphs(): void
    {
        $node = new CodeBlockNode("line1\nline2\nline3");
        $xml = $this->renderer->render($node);

        // Each line should be a separate <w:p>
        $this->assertSame(3, substr_count($xml, '<w:p>'));
    }

    public function testXmlEscapingInCode(): void
    {
        $node = new CodeBlockNode('<div class="test">');
        $xml = $this->renderer->render($node);

        $this->assertStringContainsString('&lt;div class=&quot;test&quot;&gt;', $xml);
    }
}
