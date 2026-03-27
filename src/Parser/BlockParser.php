<?php

declare(strict_types=1);

namespace Talleu\MdToOoxml\Parser;

use Talleu\MdToOoxml\Node\BlankLineNode;
use Talleu\MdToOoxml\Node\CodeBlockNode;
use Talleu\MdToOoxml\Node\DocumentNode;
use Talleu\MdToOoxml\Node\HorizontalRuleNode;
use Talleu\MdToOoxml\Node\ListItemNode;
use Talleu\MdToOoxml\Node\ParagraphNode;
use Talleu\MdToOoxml\Node\QuoteNode;
use Talleu\MdToOoxml\Node\TableCellNode;
use Talleu\MdToOoxml\Node\TableNode;
use Talleu\MdToOoxml\Node\TableRowNode;
use Talleu\MdToOoxml\Node\TitleNode;

/**
 * Built-in block-level Markdown parser (no external dependency required).
 *
 * Handles: headings, paragraphs, lists (bullet + ordered), blockquotes,
 *          fenced code blocks, horizontal rules, and tables.
 */
class BlockParser implements MarkdownParserInterface
{
    private bool $inCodeBlock = false;
    private string $codeBuffer = '';
    private ?string $codeLanguage = null;

    private bool $inTable = false;
    private ?TableNode $currentTable = null;
    private bool $tableHeaderParsed = false;

    public function __construct(
        private readonly InlineParser $inlineParser,
    ) {}

    public function parse(string $markdown): DocumentNode
    {
        $document = new DocumentNode();
        $lines = explode("\n", $markdown);

        $this->reset();

        foreach ($lines as $line) {
            if ($this->handleCodeBlock($line, $document)) {
                continue;
            }

            $trimmed = trim($line);

            // End of table context detection
            if ($this->inTable && !$this->isTableRow($trimmed)) {
                $this->flushTable($document);
            }

            if ($trimmed === '') {
                if (!$this->inTable) {
                    $document->addChild(new BlankLineNode());
                }
                continue;
            }

            if ($this->parseCodeFenceStart($trimmed)) {
                continue;
            }

            if ($this->parseHorizontalRule($trimmed, $document)) {
                continue;
            }

            if ($this->parseHeading($trimmed, $document)) {
                continue;
            }

            if ($this->parseTableRow($trimmed, $document)) {
                continue;
            }

            if ($this->parseBulletList($trimmed, $document)) {
                continue;
            }

            if ($this->parseOrderedList($trimmed, $document)) {
                continue;
            }

            if ($this->parseBlockquote($trimmed, $document)) {
                continue;
            }

            $this->parseParagraph($trimmed, $document);
        }

        // Flush any unclosed code block
        if ($this->inCodeBlock) {
            $document->addChild(new CodeBlockNode(rtrim($this->codeBuffer), $this->codeLanguage));
        }

        // Flush any pending table
        if ($this->inTable) {
            $this->flushTable($document);
        }

        return $document;
    }

    private function reset(): void
    {
        $this->inCodeBlock = false;
        $this->codeBuffer = '';
        $this->codeLanguage = null;
        $this->inTable = false;
        $this->currentTable = null;
        $this->tableHeaderParsed = false;
    }

    /**
     * Handle lines while inside a fenced code block.
     */
    private function handleCodeBlock(string $line, DocumentNode $document): bool
    {
        if (!$this->inCodeBlock) {
            return false;
        }

        if (preg_match('/^\s*```\s*$/', $line)) {
            $document->addChild(new CodeBlockNode(rtrim($this->codeBuffer), $this->codeLanguage));
            $this->inCodeBlock = false;
            $this->codeBuffer = '';
            $this->codeLanguage = null;
        } else {
            $this->codeBuffer .= $line . "\n";
        }

        return true;
    }

    private function parseCodeFenceStart(string $trimmed): bool
    {
        if (preg_match('/^```(.*)$/', $trimmed, $matches)) {
            $this->inCodeBlock = true;
            $this->codeLanguage = trim($matches[1]) ?: null;

            return true;
        }

        return false;
    }

    private function parseHorizontalRule(string $trimmed, DocumentNode $document): bool
    {
        if (preg_match('/^(\*{3,}|-{3,}|_{3,})$/', $trimmed)) {
            $document->addChild(new HorizontalRuleNode());

            return true;
        }

        return false;
    }

    private function parseHeading(string $trimmed, DocumentNode $document): bool
    {
        if (preg_match('/^(#{1,6})\s+(.*)$/', $trimmed, $matches)) {
            $level = strlen($matches[1]);
            $titleNode = new TitleNode($level);

            foreach ($this->inlineParser->parse($matches[2]) as $inline) {
                $titleNode->addChild($inline);
            }

            $document->addChild($titleNode);

            return true;
        }

        return false;
    }

    private function parseBulletList(string $trimmed, DocumentNode $document): bool
    {
        if (preg_match('/^[-*+]\s+(.*)$/', $trimmed, $matches)) {
            $listItem = new ListItemNode('bullet');

            foreach ($this->inlineParser->parse($matches[1]) as $inline) {
                $listItem->addChild($inline);
            }

            $document->addChild($listItem);

            return true;
        }

        return false;
    }

    private function parseOrderedList(string $trimmed, DocumentNode $document): bool
    {
        if (preg_match('/^\d+\.\s+(.*)$/', $trimmed, $matches)) {
            $listItem = new ListItemNode('number');

            foreach ($this->inlineParser->parse($matches[1]) as $inline) {
                $listItem->addChild($inline);
            }

            $document->addChild($listItem);

            return true;
        }

        return false;
    }

    private function parseBlockquote(string $trimmed, DocumentNode $document): bool
    {
        if (preg_match('/^>\s*(.*)$/', $trimmed, $matches)) {
            $quoteNode = new QuoteNode();

            foreach ($this->inlineParser->parse($matches[1]) as $inline) {
                $quoteNode->addChild($inline);
            }

            $document->addChild($quoteNode);

            return true;
        }

        return false;
    }

    private function isTableRow(string $trimmed): bool
    {
        return (bool) preg_match('/^\|.*\|$/', $trimmed);
    }

    private function isTableSeparator(string $trimmed): bool
    {
        return (bool) preg_match('/^\|[\s\-:|]+\|$/', $trimmed);
    }

    private function parseTableRow(string $trimmed, DocumentNode $document): bool
    {
        if (!$this->isTableRow($trimmed)) {
            return false;
        }

        // Skip the separator row (e.g. |---|---|)
        if ($this->isTableSeparator($trimmed)) {
            $this->tableHeaderParsed = true;

            return true;
        }

        if (!$this->inTable) {
            $this->inTable = true;
            $this->currentTable = new TableNode();
            $this->tableHeaderParsed = false;
        }

        $isHeader = !$this->tableHeaderParsed;
        $cells = explode('|', $trimmed);
        // Remove first and last empty elements from leading/trailing pipes
        array_shift($cells);
        array_pop($cells);

        $rowNode = new TableRowNode($isHeader);

        foreach ($cells as $cellContent) {
            $cellNode = new TableCellNode();
            foreach ($this->inlineParser->parse(trim($cellContent)) as $inline) {
                $cellNode->addChild($inline);
            }
            $rowNode->addCell($cellNode);
        }

        $this->currentTable?->addRow($rowNode);

        return true;
    }

    private function flushTable(DocumentNode $document): void
    {
        if ($this->currentTable !== null) {
            $document->addChild($this->currentTable);
        }
        $this->inTable = false;
        $this->currentTable = null;
        $this->tableHeaderParsed = false;
    }

    private function parseParagraph(string $trimmed, DocumentNode $document): void
    {
        $paragraph = new ParagraphNode();

        foreach ($this->inlineParser->parse($trimmed) as $inline) {
            $paragraph->addChild($inline);
        }

        $document->addChild($paragraph);
    }
}
