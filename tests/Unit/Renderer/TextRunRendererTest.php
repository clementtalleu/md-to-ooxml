<?php

declare(strict_types=1);

namespace Talleu\MdToOoxml\Tests\Unit\Renderer;

use PHPUnit\Framework\TestCase;
use Talleu\MdToOoxml\Node\TextRunNode;
use Talleu\MdToOoxml\Renderer\TextRunRenderer;

class TextRunRendererTest extends TestCase
{
    private TextRunRenderer $renderer;

    protected function setUp(): void
    {
        $this->renderer = new TextRunRenderer();
    }

    public function testPlainText(): void
    {
        $node = new TextRunNode('Hello');
        $xml = $this->renderer->render($node);

        $this->assertStringContainsString('<w:t xml:space="preserve">Hello</w:t>', $xml);
        $this->assertStringNotContainsString('<w:rPr>', $xml);
    }

    public function testBoldText(): void
    {
        $node = new TextRunNode('Bold', isBold: true);
        $xml = $this->renderer->render($node);

        $this->assertStringContainsString('<w:b/>', $xml);
        $this->assertStringContainsString('<w:rPr>', $xml);
    }

    public function testItalicText(): void
    {
        $node = new TextRunNode('Italic', isItalic: true);
        $xml = $this->renderer->render($node);

        $this->assertStringContainsString('<w:i/>', $xml);
    }

    public function testUnderlineText(): void
    {
        $node = new TextRunNode('Underline', isUnderline: true);
        $xml = $this->renderer->render($node);

        $this->assertStringContainsString('<w:u w:val="single"/>', $xml);
    }

    public function testStrikethroughText(): void
    {
        $node = new TextRunNode('Deleted', isStrikethrough: true);
        $xml = $this->renderer->render($node);

        $this->assertStringContainsString('<w:strike/>', $xml);
    }

    public function testXmlEscaping(): void
    {
        $node = new TextRunNode('Tom & Jerry <3 "quotes"');
        $xml = $this->renderer->render($node);

        $this->assertStringContainsString('Tom &amp; Jerry &lt;3 &quot;quotes&quot;', $xml);
    }

    public function testCombinedFormatting(): void
    {
        $node = new TextRunNode('All', isBold: true, isItalic: true, isUnderline: true, isStrikethrough: true);
        $xml = $this->renderer->render($node);

        $this->assertStringContainsString('<w:b/>', $xml);
        $this->assertStringContainsString('<w:i/>', $xml);
        $this->assertStringContainsString('<w:u w:val="single"/>', $xml);
        $this->assertStringContainsString('<w:strike/>', $xml);
    }
}
