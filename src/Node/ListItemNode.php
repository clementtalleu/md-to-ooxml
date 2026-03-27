<?php

declare(strict_types=1);

namespace Talleu\MdToOoxml\Node;

class ListItemNode extends AbstractContainerNode
{
    public function __construct(
        private readonly string $listType = 'bullet',
    ) {}

    public function getListType(): string
    {
        return $this->listType;
    }
}
