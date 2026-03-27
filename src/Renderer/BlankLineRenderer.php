<?php

declare(strict_types=1);

namespace Talleu\MdToOoxml\Renderer;

use Talleu\MdToOoxml\Node\NodeInterface;

class BlankLineRenderer implements RendererInterface
{
    public function render(NodeInterface $node): string
    {
        return '<w:p/>';
    }
}
