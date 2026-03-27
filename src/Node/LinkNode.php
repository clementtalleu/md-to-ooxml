<?php

declare(strict_types=1);

namespace Talleu\MdToOoxml\Node;

class LinkNode extends AbstractLeafNode
{
    public function __construct(
        private readonly string $text,
        private readonly string $url,
    ) {}

    public function getText(): string
    {
        return $this->text;
    }

    public function getUrl(): string
    {
        return $this->url;
    }
}
