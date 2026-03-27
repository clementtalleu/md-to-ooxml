<?php

declare(strict_types=1);

namespace Talleu\MdToOoxml\Node;

class TitleNode extends AbstractContainerNode
{
    public function __construct(
        private readonly int $level = 1,
    ) {}

    public function getLevel(): int
    {
        return $this->level;
    }
}
