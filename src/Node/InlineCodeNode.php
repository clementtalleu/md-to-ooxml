<?php

declare(strict_types=1);

namespace Talleu\MdToOoxml\Node;

class InlineCodeNode extends AbstractLeafNode
{
    public function __construct(
        private readonly string $code,
    ) {}

    public function getCode(): string
    {
        return $this->code;
    }
}
