<?php

declare(strict_types=1);

namespace Talleu\MdToOoxml\Parser\Adapter;

use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\CommonMark\Node\Block\BlockQuote;
use League\CommonMark\Extension\CommonMark\Node\Block\FencedCode;
use League\CommonMark\Extension\CommonMark\Node\Block\Heading;
use League\CommonMark\Extension\CommonMark\Node\Block\IndentedCode;
use League\CommonMark\Extension\CommonMark\Node\Block\ListBlock;
use League\CommonMark\Extension\CommonMark\Node\Block\ListItem;
use League\CommonMark\Extension\CommonMark\Node\Block\ThematicBreak;
use League\CommonMark\Extension\CommonMark\Node\Inline\Code as LeagueInlineCode;
use League\CommonMark\Extension\CommonMark\Node\Inline\Emphasis;
use League\CommonMark\Extension\CommonMark\Node\Inline\Image as LeagueImage;
use League\CommonMark\Extension\CommonMark\Node\Inline\Link;
use League\CommonMark\Extension\CommonMark\Node\Inline\Strong;
use League\CommonMark\Extension\Strikethrough\Strikethrough;
use League\CommonMark\Extension\Strikethrough\StrikethroughExtension;
use League\CommonMark\Extension\Table\Table as LeagueTable;
use League\CommonMark\Extension\Table\TableCell as LeagueTableCell;
use League\CommonMark\Extension\Table\TableExtension;
use League\CommonMark\Extension\Table\TableRow as LeagueTableRow;
use League\CommonMark\Extension\Table\TableSection;
use League\CommonMark\Node\Block\Paragraph as LeagueParagraph;
use League\CommonMark\Node\Inline\Newline as LeagueNewline;
use League\CommonMark\Node\Inline\Text as LeagueText;
use League\CommonMark\Node\Node as LeagueNode;
use League\CommonMark\Parser\MarkdownParser;
use RuntimeException;
use Talleu\MdToOoxml\Node\CodeBlockNode;
use Talleu\MdToOoxml\Node\DocumentNode;
use Talleu\MdToOoxml\Node\HorizontalRuleNode;
use Talleu\MdToOoxml\Node\ImageNode;
use Talleu\MdToOoxml\Node\InlineCodeNode;
use Talleu\MdToOoxml\Node\LinkNode;
use Talleu\MdToOoxml\Node\ListItemNode;
use Talleu\MdToOoxml\Node\NodeInterface;
use Talleu\MdToOoxml\Node\ParagraphNode;
use Talleu\MdToOoxml\Node\QuoteNode;
use Talleu\MdToOoxml\Node\TableCellNode;
use Talleu\MdToOoxml\Node\TableNode;
use Talleu\MdToOoxml\Node\TableRowNode;
use Talleu\MdToOoxml\Node\TextRunNode;
use Talleu\MdToOoxml\Node\TitleNode;
use Talleu\MdToOoxml\Parser\MarkdownParserInterface;

/**
 * Adapter that uses league/commonmark to parse Markdown into our AST.
 *
 * Requires: league/commonmark ^2.0
 */
class LeagueCommonMarkAdapter implements MarkdownParserInterface
{
    private MarkdownParser $leagueParser;

    public function __construct()
    {
        if (!class_exists(MarkdownParser::class)) {
            throw new RuntimeException(
                'league/commonmark is required to use this adapter. Install it with: composer require league/commonmark',
            );
        }

        $environment = new Environment();
        $environment->addExtension(new CommonMarkCoreExtension());

        if (class_exists(TableExtension::class)) {
            $environment->addExtension(new TableExtension());
        }
        if (class_exists(StrikethroughExtension::class)) {
            $environment->addExtension(new StrikethroughExtension());
        }

        $this->leagueParser = new MarkdownParser($environment);
    }

    public function parse(string $markdown): DocumentNode
    {
        $leagueDocument = $this->leagueParser->parse($markdown);
        $documentNode = new DocumentNode();
        $this->mapChildren($leagueDocument, $documentNode);

        return $documentNode;
    }

    /**
     * @param array{is_bold?: bool, is_italic?: bool, is_strikethrough?: bool, list_type?: string} $context
     */
    private function mapChildren(LeagueNode $leagueParent, NodeInterface $talleuParent, array $context = []): void
    {
        foreach ($leagueParent->children() as $child) {
            $this->mapNode($child, $talleuParent, $context);
        }
    }

    /**
     * Recursively translate a League node into our own AST node.
     *
     * @param array{is_bold?: bool, is_italic?: bool, is_strikethrough?: bool, list_type?: string} $context
     */
    private function mapNode(LeagueNode $node, NodeInterface $talleuParent, array $context): void
    {
        // --- Block-level nodes ---

        if ($node instanceof LeagueParagraph) {
            // Inside a list item or blockquote, League wraps text in a Paragraph — we skip it
            // to avoid nested <w:p> elements (invalid OOXML).
            if ($talleuParent instanceof ListItemNode || $talleuParent instanceof QuoteNode) {
                $this->mapChildren($node, $talleuParent, $context);
            } else {
                $paragraphNode = new ParagraphNode();
                $talleuParent->addChild($paragraphNode);
                $this->mapChildren($node, $paragraphNode, $context);
            }

            return;
        }

        if ($node instanceof Heading) {
            $titleNode = new TitleNode($node->getLevel());
            $talleuParent->addChild($titleNode);
            $this->mapChildren($node, $titleNode, $context);

            return;
        }

        if ($node instanceof BlockQuote) {
            $quoteNode = new QuoteNode();
            $talleuParent->addChild($quoteNode);
            $this->mapChildren($node, $quoteNode, $context);

            return;
        }

        if ($node instanceof ListBlock) {
            $context['list_type'] = $node->getListData()->type === ListBlock::TYPE_BULLET ? 'bullet' : 'number';
            $this->mapChildren($node, $talleuParent, $context);

            return;
        }

        if ($node instanceof ListItem) {
            $listItemNode = new ListItemNode($context['list_type'] ?? 'bullet');
            $talleuParent->addChild($listItemNode);
            $this->mapChildren($node, $listItemNode, $context);

            return;
        }

        if ($node instanceof FencedCode) {
            $talleuParent->addChild(new CodeBlockNode(
                rtrim($node->getLiteral()),
                $node->getInfo() ?: null,
            ));

            return;
        }

        if ($node instanceof IndentedCode) {
            $talleuParent->addChild(new CodeBlockNode(rtrim($node->getLiteral())));

            return;
        }

        if ($node instanceof ThematicBreak) {
            $talleuParent->addChild(new HorizontalRuleNode());

            return;
        }

        // --- Table support (league/commonmark table extension) ---

        if ($node instanceof LeagueTable) {
            $tableNode = new TableNode();
            $talleuParent->addChild($tableNode);
            // Table contains TableSection (head/body) which contains TableRow
            foreach ($node->children() as $section) {
                if ($section instanceof TableSection) {
                    $isHeader = $section->getType() === TableSection::TYPE_HEAD;
                    foreach ($section->children() as $row) {
                        if ($row instanceof LeagueTableRow) {
                            $rowNode = new TableRowNode($isHeader);
                            foreach ($row->children() as $cell) {
                                if ($cell instanceof LeagueTableCell) {
                                    $cellNode = new TableCellNode();
                                    $this->mapChildren($cell, $cellNode, $context);
                                    $rowNode->addCell($cellNode);
                                }
                            }
                            $tableNode->addRow($rowNode);
                        }
                    }
                }
            }

            return;
        }

        // --- Inline nodes ---

        if ($node instanceof Strong) {
            $context['is_bold'] = true;
            $this->mapChildren($node, $talleuParent, $context);

            return;
        }

        if ($node instanceof Emphasis) {
            $context['is_italic'] = true;
            $this->mapChildren($node, $talleuParent, $context);

            return;
        }

        if ($node instanceof Strikethrough) {
            $context['is_strikethrough'] = true;
            $this->mapChildren($node, $talleuParent, $context);

            return;
        }

        if ($node instanceof LeagueInlineCode) {
            $talleuParent->addChild(new InlineCodeNode($node->getLiteral()));

            return;
        }

        if ($node instanceof LeagueText) {
            $talleuParent->addChild(new TextRunNode(
                $node->getLiteral(),
                isBold: $context['is_bold'] ?? false,
                isItalic: $context['is_italic'] ?? false,
                isStrikethrough: $context['is_strikethrough'] ?? false,
            ));

            return;
        }

        if ($node instanceof Link) {
            $talleuParent->addChild(new LinkNode(
                $this->extractPlainText($node),
                $node->getUrl(),
            ));

            return;
        }

        if ($node instanceof LeagueImage) {
            $talleuParent->addChild(new ImageNode(
                $this->extractPlainText($node),
                $node->getUrl(),
            ));

            return;
        }

        if ($node instanceof LeagueNewline) {
            return;
        }

        // Fallback: try to map children for unknown container nodes
        $this->mapChildren($node, $talleuParent, $context);
    }

    private function extractPlainText(LeagueNode $node): string
    {
        $text = '';
        $walker = $node->walker();

        while ($event = $walker->next()) {
            if ($event->isEntering() && $event->getNode() instanceof LeagueText) {
                $text .= $event->getNode()->getLiteral();
            }
        }

        return $text;
    }
}
