<?php

declare(strict_types=1);

namespace Talleu\MdToOoxml\Tests\Unit\Parser;

use PHPUnit\Framework\TestCase;
use Talleu\MdToOoxml\Node\BlankLineNode;
use Talleu\MdToOoxml\Node\CodeBlockNode;
use Talleu\MdToOoxml\Node\HorizontalRuleNode;
use Talleu\MdToOoxml\Node\ListItemNode;
use Talleu\MdToOoxml\Node\ParagraphNode;
use Talleu\MdToOoxml\Node\QuoteNode;
use Talleu\MdToOoxml\Node\TableNode;
use Talleu\MdToOoxml\Node\TextRunNode;
use Talleu\MdToOoxml\Node\TitleNode;
use Talleu\MdToOoxml\Parser\BlockParser;
use Talleu\MdToOoxml\Parser\InlineParser;

class BlockParserTest extends TestCase
{
    private BlockParser $parser;

    protected function setUp(): void
    {
        $this->parser = new BlockParser(new InlineParser());
    }

    public function testHeadings(): void
    {
        for ($level = 1; $level <= 6; $level++) {
            $hashes = str_repeat('#', $level);
            $doc = $this->parser->parse("{$hashes} Heading {$level}");
            $children = $doc->getChildren();

            $this->assertCount(1, $children, "Heading level {$level} should produce 1 child");
            $this->assertInstanceOf(TitleNode::class, $children[0]);
            $this->assertSame($level, $children[0]->getLevel());
        }
    }

    public function testParagraph(): void
    {
        $doc = $this->parser->parse('Just a paragraph.');
        $children = $doc->getChildren();

        $this->assertCount(1, $children);
        $this->assertInstanceOf(ParagraphNode::class, $children[0]);

        $inlines = $children[0]->getChildren();
        $this->assertCount(1, $inlines);
        $this->assertInstanceOf(TextRunNode::class, $inlines[0]);
        $this->assertSame('Just a paragraph.', $inlines[0]->getText());
    }

    public function testBlankLine(): void
    {
        $doc = $this->parser->parse("First\n\nSecond");
        $children = $doc->getChildren();

        $this->assertCount(3, $children);
        $this->assertInstanceOf(ParagraphNode::class, $children[0]);
        $this->assertInstanceOf(BlankLineNode::class, $children[1]);
        $this->assertInstanceOf(ParagraphNode::class, $children[2]);
    }

    public function testBulletList(): void
    {
        $md = "- Item one\n- Item two\n* Item three";
        $doc = $this->parser->parse($md);
        $children = $doc->getChildren();

        $this->assertCount(3, $children);

        foreach ($children as $child) {
            $this->assertInstanceOf(ListItemNode::class, $child);
            $this->assertSame('bullet', $child->getListType());
        }
    }

    public function testOrderedList(): void
    {
        $md = "1. First\n2. Second\n3. Third";
        $doc = $this->parser->parse($md);
        $children = $doc->getChildren();

        $this->assertCount(3, $children);

        foreach ($children as $child) {
            $this->assertInstanceOf(ListItemNode::class, $child);
            $this->assertSame('number', $child->getListType());
        }
    }

    public function testBlockquote(): void
    {
        $doc = $this->parser->parse('> This is a quote');
        $children = $doc->getChildren();

        $this->assertCount(1, $children);
        $this->assertInstanceOf(QuoteNode::class, $children[0]);
    }

    public function testFencedCodeBlock(): void
    {
        $md = "```php\necho 'hello';\n```";
        $doc = $this->parser->parse($md);
        $children = $doc->getChildren();

        $this->assertCount(1, $children);
        $this->assertInstanceOf(CodeBlockNode::class, $children[0]);
        $this->assertSame("echo 'hello';", $children[0]->getCode());
        $this->assertSame('php', $children[0]->getLanguage());
    }

    public function testCodeBlockWithoutLanguage(): void
    {
        $md = "```\nsome code\n```";
        $doc = $this->parser->parse($md);
        $children = $doc->getChildren();

        $this->assertCount(1, $children);
        $this->assertInstanceOf(CodeBlockNode::class, $children[0]);
        $this->assertNull($children[0]->getLanguage());
    }

    public function testHorizontalRule(): void
    {
        $variants = ['---', '***', '___', '-----'];

        foreach ($variants as $variant) {
            $doc = $this->parser->parse($variant);
            $children = $doc->getChildren();

            $this->assertCount(1, $children, "Horizontal rule variant '{$variant}' should produce 1 child");
            $this->assertInstanceOf(HorizontalRuleNode::class, $children[0]);
        }
    }

    public function testTable(): void
    {
        $md = "| Name | Age |\n| --- | --- |\n| Alice | 30 |\n| Bob | 25 |";
        $doc = $this->parser->parse($md);
        $children = $doc->getChildren();

        $this->assertCount(1, $children);
        $this->assertInstanceOf(TableNode::class, $children[0]);

        $rows = $children[0]->getRows();
        $this->assertCount(3, $rows); // header + 2 data rows

        $this->assertTrue($rows[0]->isHeader());
        $this->assertFalse($rows[1]->isHeader());
        $this->assertFalse($rows[2]->isHeader());

        $headerCells = $rows[0]->getCells();
        $this->assertCount(2, $headerCells);
    }

    public function testComplexDocument(): void
    {
        $md = <<<'MD'
            # Title

            A paragraph with **bold** text.

            - Bullet one
            - Bullet two

            > A quote

            ---

            ```js
            console.log('hello');
            ```
            MD;

        $doc = $this->parser->parse($md);
        $children = $doc->getChildren();

        // Title, blank, paragraph, blank, bullet, bullet, blank, quote, blank, hr, blank, code
        $this->assertGreaterThan(5, count($children));

        $this->assertInstanceOf(TitleNode::class, $children[0]);
    }

    public function testUnclosedCodeBlockIsFlushed(): void
    {
        $md = "```\nunclosed code";
        $doc = $this->parser->parse($md);
        $children = $doc->getChildren();

        $this->assertCount(1, $children);
        $this->assertInstanceOf(CodeBlockNode::class, $children[0]);
        $this->assertSame('unclosed code', $children[0]->getCode());
    }

    public function testPlusSignBulletList(): void
    {
        $doc = $this->parser->parse('+ Item with plus');
        $children = $doc->getChildren();

        $this->assertCount(1, $children);
        $this->assertInstanceOf(ListItemNode::class, $children[0]);
        $this->assertSame('bullet', $children[0]->getListType());
    }

    public function testEmptyBlockquote(): void
    {
        $doc = $this->parser->parse('> ');
        $children = $doc->getChildren();

        $this->assertCount(1, $children);
        $this->assertInstanceOf(QuoteNode::class, $children[0]);
    }
}
