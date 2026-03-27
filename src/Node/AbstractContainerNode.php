<?php

declare(strict_types=1);

namespace Talleu\MdToOoxml\Node;

/**
 * Base class for nodes that contain child nodes.
 */
abstract class AbstractContainerNode implements NodeInterface
{
    /** @var NodeInterface[] */
    private array $children = [];

    public function addChild(NodeInterface $node): void
    {
        $this->children[] = $node;
    }

    /** @return NodeInterface[] */
    public function getChildren(): array
    {
        return $this->children;
    }
}
