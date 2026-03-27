<?php

declare(strict_types=1);

namespace Talleu\MdToOoxml\Tests\Integration;

use PHPUnit\Framework\TestCase;
use Talleu\MdToOoxml\OoXmlConverterFactory;

/**
 * Integration tests for the League CommonMark adapter.
 */
class CommonMarkAdapterTest extends TestCase
{
    protected function setUp(): void
    {
        if (!class_exists(\League\CommonMark\Parser\MarkdownParser::class)) {
            $this->markTestSkipped('league/commonmark is required for this test.');
        }
    }

    public function testBasicConversion(): void
    {
        $converter = OoXmlConverterFactory::createWithCommonMark();

        $xml = $converter->convert("# Title\n\nA paragraph.");

        $this->assertStringContainsString('Heading1', $xml);
        $this->assertStringContainsString('Title', $xml);
        $this->assertStringContainsString('A paragraph.', $xml);
    }

    public function testBoldAndItalic(): void
    {
        $converter = OoXmlConverterFactory::createWithCommonMark();

        $xml = $converter->convert('**bold** and *italic*');

        $this->assertStringContainsString('<w:b/>', $xml);
        $this->assertStringContainsString('<w:i/>', $xml);
    }

    public function testCodeBlock(): void
    {
        $converter = OoXmlConverterFactory::createWithCommonMark();

        $xml = $converter->convert("```\ncode here\n```");

        $this->assertStringContainsString('Courier New', $xml);
        $this->assertStringContainsString('code here', $xml);
    }

    public function testLink(): void
    {
        $converter = OoXmlConverterFactory::createWithCommonMark();

        $xml = $converter->convert('[Example](https://example.com)');

        $this->assertStringContainsString('HYPERLINK', $xml);
        $this->assertStringContainsString('https://example.com', $xml);
    }

    public function testLists(): void
    {
        $converter = OoXmlConverterFactory::createWithCommonMark();

        $xml = $converter->convert("- Item 1\n- Item 2");

        $this->assertStringContainsString('<w:numId', $xml);
        $this->assertStringContainsString('Item 1', $xml);
    }

    public function testBlockquote(): void
    {
        $converter = OoXmlConverterFactory::createWithCommonMark();

        $xml = $converter->convert('> A quote');

        $this->assertStringContainsString('<w:ind w:left="720"/>', $xml);
        $this->assertStringContainsString('A quote', $xml);
    }

    public function testHorizontalRule(): void
    {
        $converter = OoXmlConverterFactory::createWithCommonMark();

        $xml = $converter->convert("Above\n\n---\n\nBelow");

        $this->assertStringContainsString('<w:pBdr>', $xml);
    }

    public function testInlineCode(): void
    {
        $converter = OoXmlConverterFactory::createWithCommonMark();

        $xml = $converter->convert('Use `code` here');

        $this->assertStringContainsString('Courier New', $xml);
    }

    public function testComprehensiveDocument(): void
    {
        $converter = OoXmlConverterFactory::createWithCommonMark();

        $md = <<<'MD'
            # Title

            Paragraph with **bold**, *italic*, and `code`.

            - Bullet 1
            - Bullet 2

            1. Ordered 1
            2. Ordered 2

            > A blockquote

            ```php
            echo "hello";
            ```

            ---

            [A link](https://example.com)
            MD;

        $xml = $converter->convert($md);

        $this->assertStringContainsString('Heading1', $xml);
        $this->assertStringContainsString('<w:b/>', $xml);
        $this->assertStringContainsString('<w:i/>', $xml);
        $this->assertStringContainsString('Courier New', $xml);
        $this->assertStringContainsString('<w:numId', $xml);
        $this->assertStringContainsString('<w:ind w:left="720"/>', $xml);
        $this->assertStringContainsString('HYPERLINK', $xml);
    }

    public function testBodyXmlOnly(): void
    {
        $converter = OoXmlConverterFactory::createWithCommonMark();

        $xml = $converter->convertToBodyXml('Hello **world**');

        $this->assertStringNotContainsString('<?xml', $xml);
        $this->assertStringContainsString('Hello ', $xml);
        $this->assertStringContainsString('<w:b/>', $xml);
    }
}
