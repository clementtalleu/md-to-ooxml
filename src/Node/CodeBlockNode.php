<?php

declare(strict_types=1);

namespace Talleu\MdToOoxml\Node;

class CodeBlockNode extends AbstractLeafNode
{
    public function __construct(
        private readonly string $code,
        private readonly ?string $language = null,
    ) {}

    public function getCode(): string
    {
        return $this->code;
    }

    public function getLanguage(): ?string
    {
        return $this->language;
    }
}
