<?php

declare(strict_types=1);

namespace Talleu\MdToOoxml\Node;

class TableRowNode extends AbstractLeafNode
{
    /** @var TableCellNode[] */
    private array $cells = [];

    public function __construct(
        private readonly bool $isHeader = false,
    ) {}

    public function addCell(TableCellNode $cell): void
    {
        $this->cells[] = $cell;
    }

    /** @return TableCellNode[] */
    public function getCells(): array
    {
        return $this->cells;
    }

    public function isHeader(): bool
    {
        return $this->isHeader;
    }
}
