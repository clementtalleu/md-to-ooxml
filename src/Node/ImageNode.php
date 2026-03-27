<?php

declare(strict_types=1);

namespace Talleu\MdToOoxml\Node;

class ImageNode extends AbstractLeafNode
{
    public function __construct(
        private readonly string $altText,
        private readonly string $url,
    ) {}

    public function getAltText(): string
    {
        return $this->altText;
    }

    public function getUrl(): string
    {
        return $this->url;
    }
}
