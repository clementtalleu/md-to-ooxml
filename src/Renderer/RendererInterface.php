<?php

declare(strict_types=1);

namespace Talleu\MdToOoxml\Renderer;

use Talleu\MdToOoxml\Node\NodeInterface;

interface RendererInterface
{
    /**
     * Render an AST node to an OOXML string.
     */
    public function render(NodeInterface $node): string;
}
