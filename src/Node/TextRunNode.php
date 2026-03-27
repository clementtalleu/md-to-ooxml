<?php

declare(strict_types=1);

namespace Talleu\MdToOoxml\Node;

class TextRunNode extends AbstractLeafNode
{
    public function __construct(
        private readonly string $text,
        private readonly bool $isBold = false,
        private readonly bool $isItalic = false,
        private readonly bool $isUnderline = false,
        private readonly bool $isStrikethrough = false,
    ) {}

    public function getText(): string
    {
        return $this->text;
    }

    public function isBold(): bool
    {
        return $this->isBold;
    }

    public function isItalic(): bool
    {
        return $this->isItalic;
    }

    public function isUnderline(): bool
    {
        return $this->isUnderline;
    }

    public function isStrikethrough(): bool
    {
        return $this->isStrikethrough;
    }
}
