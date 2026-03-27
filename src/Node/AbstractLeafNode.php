<?php

declare(strict_types=1);

namespace Talleu\MdToOoxml\Node;

/**
 * Base class for leaf nodes (no children).
 */
abstract class AbstractLeafNode implements NodeInterface
{
    public function addChild(NodeInterface $node): void
    {
        // Leaf node: children are not supported.
    }

    /** @return NodeInterface[] */
    public function getChildren(): array
    {
        return [];
    }
}
