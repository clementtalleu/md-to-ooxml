<?php

declare(strict_types=1);

namespace Talleu\MdToOoxml\Tests\Integration;

use PHPUnit\Framework\TestCase;
use Talleu\MdToOoxml\OoXmlConverterFactory;

/**
 * Integration tests: full Markdown → OOXML conversion pipeline.
 */
class OoXmlConverterTest extends TestCase
{
    public function testFullConversion(): void
    {
        $converter = OoXmlConverterFactory::create();

        $markdown = "## Heading 2\nThis is a **test**.";
        $xml = $converter->convert($markdown);

        $this->assertStringStartsWith('<?xml version="1.0" encoding="UTF-8" standalone="yes"?>', $xml);
        $this->assertStringContainsString('<w:document', $xml);
        $this->assertStringContainsString('<w:body>', $xml);
        $this->assertStringContainsString('<w:b/>', $xml);
        $this->assertStringContainsString('test', $xml);
    }

    public function testConvertToBodyXml(): void
    {
        $converter = OoXmlConverterFactory::create();

        $xml = $converter->convertToBodyXml('Hello **world**');

        // Should NOT contain the document envelope
        $this->assertStringNotContainsString('<?xml', $xml);
        $this->assertStringNotContainsString('<w:document', $xml);

        // Should contain the actual content
        $this->assertStringContainsString('<w:p>', $xml);
        $this->assertStringContainsString('Hello ', $xml);
        $this->assertStringContainsString('<w:b/>', $xml);
        $this->assertStringContainsString('world', $xml);
    }

    public function testHeadingsUseWordStyles(): void
    {
        $converter = OoXmlConverterFactory::create();

        $xml = $converter->convert("# H1\n## H2\n### H3");

        $this->assertStringContainsString('Heading1', $xml);
        $this->assertStringContainsString('Heading2', $xml);
        $this->assertStringContainsString('Heading3', $xml);
    }

    public function testBulletList(): void
    {
        $converter = OoXmlConverterFactory::create();

        $xml = $converter->convert("- First\n- Second");

        $this->assertStringContainsString('<w:numId w:val="1"/>', $xml);
        $this->assertStringContainsString('First', $xml);
        $this->assertStringContainsString('Second', $xml);
    }

    public function testOrderedList(): void
    {
        $converter = OoXmlConverterFactory::create();

        $xml = $converter->convert("1. First\n2. Second");

        $this->assertStringContainsString('<w:numId w:val="2"/>', $xml);
    }

    public function testBlockquote(): void
    {
        $converter = OoXmlConverterFactory::create();

        $xml = $converter->convert('> A wise quote');

        $this->assertStringContainsString('<w:ind w:left="720"/>', $xml);
        $this->assertStringContainsString('A wise quote', $xml);
    }

    public function testCodeBlock(): void
    {
        $converter = OoXmlConverterFactory::create();

        $xml = $converter->convert("```php\necho 'hello';\n```");

        $this->assertStringContainsString('Courier New', $xml);
        $this->assertStringContainsString('echo', $xml);
    }

    public function testInlineCode(): void
    {
        $converter = OoXmlConverterFactory::create();

        $xml = $converter->convert('Use `composer` for packages');

        $this->assertStringContainsString('Courier New', $xml);
        $this->assertStringContainsString('composer', $xml);
    }

    public function testLink(): void
    {
        $converter = OoXmlConverterFactory::create();

        $xml = $converter->convert('[Google](https://google.com)');

        $this->assertStringContainsString('HYPERLINK', $xml);
        $this->assertStringContainsString('https://google.com', $xml);
        $this->assertStringContainsString('Google', $xml);
    }

    public function testHorizontalRule(): void
    {
        $converter = OoXmlConverterFactory::create();

        $xml = $converter->convert("Above\n\n---\n\nBelow");

        $this->assertStringContainsString('<w:pBdr>', $xml);
    }

    public function testStrikethrough(): void
    {
        $converter = OoXmlConverterFactory::create();

        $xml = $converter->convert('Some ~~deleted~~ text');

        $this->assertStringContainsString('<w:strike/>', $xml);
        $this->assertStringContainsString('deleted', $xml);
    }

    public function testBoldItalicCombination(): void
    {
        $converter = OoXmlConverterFactory::create();

        $xml = $converter->convert('This is ***bold and italic***');

        $this->assertStringContainsString('<w:b/>', $xml);
        $this->assertStringContainsString('<w:i/>', $xml);
    }

    public function testTable(): void
    {
        $converter = OoXmlConverterFactory::create();

        $md = "| Name | Age |\n| --- | --- |\n| Alice | 30 |";
        $xml = $converter->convert($md);

        $this->assertStringContainsString('<w:tbl>', $xml);
        $this->assertStringContainsString('<w:tr>', $xml);
        $this->assertStringContainsString('<w:tc>', $xml);
        $this->assertStringContainsString('Name', $xml);
        $this->assertStringContainsString('Alice', $xml);
    }

    public function testImage(): void
    {
        $converter = OoXmlConverterFactory::create();

        $xml = $converter->convert('![Alt Text](https://example.com/img.png)');

        $this->assertStringContainsString('Alt Text', $xml);
        $this->assertStringContainsString('https://example.com/img.png', $xml);
    }

    public function testEmptyMarkdownProducesValidDocument(): void
    {
        $converter = OoXmlConverterFactory::create();

        $xml = $converter->convert('');

        $this->assertStringStartsWith('<?xml', $xml);
        $this->assertStringContainsString('<w:body>', $xml);
    }

    public function testXmlSpecialCharactersAreEscaped(): void
    {
        $converter = OoXmlConverterFactory::create();

        $xml = $converter->convert('Tom & Jerry <3');

        $this->assertStringContainsString('Tom &amp; Jerry &lt;3', $xml);
    }
}
