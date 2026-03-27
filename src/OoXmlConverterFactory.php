<?php

declare(strict_types=1);

namespace Talleu\MdToOoxml;

use RuntimeException;
use Talleu\MdToOoxml\Node\BlankLineNode;
use Talleu\MdToOoxml\Node\CodeBlockNode;
use Talleu\MdToOoxml\Node\DocumentNode;
use Talleu\MdToOoxml\Node\HorizontalRuleNode;
use Talleu\MdToOoxml\Node\ImageNode;
use Talleu\MdToOoxml\Node\InlineCodeNode;
use Talleu\MdToOoxml\Node\LinkNode;
use Talleu\MdToOoxml\Node\ListItemNode;
use Talleu\MdToOoxml\Node\ParagraphNode;
use Talleu\MdToOoxml\Node\QuoteNode;
use Talleu\MdToOoxml\Node\TableNode;
use Talleu\MdToOoxml\Node\TextRunNode;
use Talleu\MdToOoxml\Node\TitleNode;
use Talleu\MdToOoxml\Parser\BlockParser;
use Talleu\MdToOoxml\Parser\InlineParser;
use Talleu\MdToOoxml\Parser\MarkdownParserInterface;
use Talleu\MdToOoxml\Renderer\BlankLineRenderer;
use Talleu\MdToOoxml\Renderer\CodeBlockRenderer;
use Talleu\MdToOoxml\Renderer\DocumentRenderer;
use Talleu\MdToOoxml\Renderer\HorizontalRuleRenderer;
use Talleu\MdToOoxml\Renderer\ImageRenderer;
use Talleu\MdToOoxml\Renderer\InlineCodeRenderer;
use Talleu\MdToOoxml\Renderer\LinkRenderer;
use Talleu\MdToOoxml\Renderer\ListItemRenderer;
use Talleu\MdToOoxml\Renderer\NodeRenderer;
use Talleu\MdToOoxml\Renderer\ParagraphRenderer;
use Talleu\MdToOoxml\Renderer\QuoteRenderer;
use Talleu\MdToOoxml\Renderer\TableRenderer;
use Talleu\MdToOoxml\Renderer\TextRunRenderer;
use Talleu\MdToOoxml\Renderer\TitleRenderer;

/**
 * Factory to create a fully configured OoXmlConverter instance.
 */
class OoXmlConverterFactory
{
    /**
     * Create a converter using the built-in BlockParser (no external dependency).
     */
    public static function create(): OoXmlConverter
    {
        $parser = new BlockParser(new InlineParser());

        return new OoXmlConverter($parser, self::buildNodeRenderer());
    }

    /**
     * Create a converter using the League CommonMark adapter.
     *
     * Requires: league/commonmark ^2.0
     */
    public static function createWithCommonMark(): OoXmlConverter
    {
        if (!class_exists(\League\CommonMark\Parser\MarkdownParser::class)) {
            throw new RuntimeException(
                'league/commonmark is required. Install it with: composer require league/commonmark',
            );
        }

        $parser = new \Talleu\MdToOoxml\Parser\Adapter\LeagueCommonMarkAdapter();

        return new OoXmlConverter($parser, self::buildNodeRenderer());
    }

    /**
     * Create a converter with a custom parser implementation.
     */
    public static function createWithParser(MarkdownParserInterface $parser): OoXmlConverter
    {
        return new OoXmlConverter($parser, self::buildNodeRenderer());
    }

    private static function buildNodeRenderer(): NodeRenderer
    {
        $renderer = new NodeRenderer();

        $renderer->addRenderer(TextRunNode::class, new TextRunRenderer());
        $renderer->addRenderer(InlineCodeNode::class, new InlineCodeRenderer());
        $renderer->addRenderer(LinkNode::class, new LinkRenderer());
        $renderer->addRenderer(ImageNode::class, new ImageRenderer());
        $renderer->addRenderer(ParagraphNode::class, new ParagraphRenderer($renderer));
        $renderer->addRenderer(ListItemNode::class, new ListItemRenderer($renderer));
        $renderer->addRenderer(QuoteNode::class, new QuoteRenderer($renderer));
        $renderer->addRenderer(TitleNode::class, new TitleRenderer($renderer));
        $renderer->addRenderer(CodeBlockNode::class, new CodeBlockRenderer());
        $renderer->addRenderer(BlankLineNode::class, new BlankLineRenderer());
        $renderer->addRenderer(HorizontalRuleNode::class, new HorizontalRuleRenderer());
        $renderer->addRenderer(TableNode::class, new TableRenderer($renderer));
        $renderer->addRenderer(DocumentNode::class, new DocumentRenderer($renderer));

        return $renderer;
    }
}
