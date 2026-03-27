<?php

declare(strict_types=1);

namespace Talleu\MdToOoxml\Renderer;

use InvalidArgumentException;
use Talleu\MdToOoxml\Node\NodeInterface;

/**
 * Registry that dispatches rendering to the correct RendererInterface
 * based on the FQCN of the node being rendered.
 */
class NodeRenderer
{
    /** @var array<class-string<NodeInterface>, RendererInterface> */
    private array $renderers = [];

    /**
     * @param class-string<NodeInterface> $nodeClass
     */
    public function addRenderer(string $nodeClass, RendererInterface $renderer): void
    {
        $this->renderers[$nodeClass] = $renderer;
    }

    public function render(NodeInterface $node): string
    {
        $nodeClass = $node::class;

        if (!isset($this->renderers[$nodeClass])) {
            throw new InvalidArgumentException(sprintf(
                'No renderer registered for node class "%s".',
                $nodeClass,
            ));
        }

        return $this->renderers[$nodeClass]->render($node);
    }
}
