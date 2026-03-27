<?php

declare(strict_types=1);

namespace Talleu\MdToOoxml\Node;

/**
 * Represents a full table with rows.
 */
class TableNode extends AbstractLeafNode
{
    /** @var TableRowNode[] */
    private array $rows = [];

    public function addRow(TableRowNode $row): void
    {
        $this->rows[] = $row;
    }

    /** @return TableRowNode[] */
    public function getRows(): array
    {
        return $this->rows;
    }
}
