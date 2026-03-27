<?php

declare(strict_types=1);

namespace Talleu\MdToOoxml\Node;

interface NodeInterface
{
    public function addChild(NodeInterface $node): void;

    /** @return NodeInterface[] */
    public function getChildren(): array;
}
