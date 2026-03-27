<?php

declare(strict_types=1);

namespace Talleu\MdToOoxml\Tests\Unit\Parser;

use PHPUnit\Framework\TestCase;
use Talleu\MdToOoxml\Node\ImageNode;
use Talleu\MdToOoxml\Node\InlineCodeNode;
use Talleu\MdToOoxml\Node\LinkNode;
use Talleu\MdToOoxml\Node\TextRunNode;
use Talleu\MdToOoxml\Parser\InlineParser;

class InlineParserTest extends TestCase
{
    private InlineParser $parser;

    protected function setUp(): void
    {
        $this->parser = new InlineParser();
    }

    public function testPlainText(): void
    {
        $nodes = $this->parser->parse('Hello world');

        $this->assertCount(1, $nodes);
        $this->assertInstanceOf(TextRunNode::class, $nodes[0]);
        $this->assertSame('Hello world', $nodes[0]->getText());
        $this->assertFalse($nodes[0]->isBold());
        $this->assertFalse($nodes[0]->isItalic());
    }

    public function testBoldWithAsterisks(): void
    {
        $nodes = $this->parser->parse('Some **bold** text');

        $this->assertCount(3, $nodes);
        $this->assertInstanceOf(TextRunNode::class, $nodes[1]);
        $this->assertSame('bold', $nodes[1]->getText());
        $this->assertTrue($nodes[1]->isBold());
    }

    public function testItalicWithAsterisks(): void
    {
        $nodes = $this->parser->parse('Some *italic* text');

        $this->assertCount(3, $nodes);
        $this->assertInstanceOf(TextRunNode::class, $nodes[1]);
        $this->assertSame('italic', $nodes[1]->getText());
        $this->assertTrue($nodes[1]->isItalic());
    }

    public function testItalicWithUnderscores(): void
    {
        $nodes = $this->parser->parse('Some _italic_ text');

        $this->assertCount(3, $nodes);
        $this->assertSame('italic', $nodes[1]->getText());
        $this->assertTrue($nodes[1]->isItalic());
    }

    public function testUnderline(): void
    {
        $nodes = $this->parser->parse('Some __underlined__ text');

        $this->assertCount(3, $nodes);
        $this->assertSame('underlined', $nodes[1]->getText());
        $this->assertTrue($nodes[1]->isUnderline());
    }

    public function testBoldItalic(): void
    {
        $nodes = $this->parser->parse('Some ***bold italic*** text');

        $this->assertCount(3, $nodes);
        $this->assertSame('bold italic', $nodes[1]->getText());
        $this->assertTrue($nodes[1]->isBold());
        $this->assertTrue($nodes[1]->isItalic());
    }

    public function testStrikethrough(): void
    {
        $nodes = $this->parser->parse('Some ~~deleted~~ text');

        $this->assertCount(3, $nodes);
        $this->assertSame('deleted', $nodes[1]->getText());
        $this->assertTrue($nodes[1]->isStrikethrough());
    }

    public function testInlineCode(): void
    {
        $nodes = $this->parser->parse('Use `composer install` to install');

        $this->assertCount(3, $nodes);
        $this->assertInstanceOf(InlineCodeNode::class, $nodes[1]);
        $this->assertSame('composer install', $nodes[1]->getCode());
    }

    public function testLink(): void
    {
        $nodes = $this->parser->parse('Visit [Google](https://google.com) now');

        $this->assertCount(3, $nodes);
        $this->assertInstanceOf(LinkNode::class, $nodes[1]);
        $this->assertSame('Google', $nodes[1]->getText());
        $this->assertSame('https://google.com', $nodes[1]->getUrl());
    }

    public function testImage(): void
    {
        $nodes = $this->parser->parse('See ![logo](https://example.com/logo.png) here');

        $this->assertCount(3, $nodes);
        $this->assertInstanceOf(ImageNode::class, $nodes[1]);
        $this->assertSame('logo', $nodes[1]->getAltText());
        $this->assertSame('https://example.com/logo.png', $nodes[1]->getUrl());
    }

    public function testMixedInlineFormatting(): void
    {
        $nodes = $this->parser->parse('Normal **bold** and *italic* end');

        $this->assertCount(5, $nodes);
        $this->assertSame('Normal ', $nodes[0]->getText());
        $this->assertTrue($nodes[1]->isBold());
        $this->assertSame(' and ', $nodes[2]->getText());
        $this->assertTrue($nodes[3]->isItalic());
        $this->assertSame(' end', $nodes[4]->getText());
    }

    public function testEmptyStringReturnsNoNodes(): void
    {
        $nodes = $this->parser->parse('');

        $this->assertCount(0, $nodes);
    }
}
