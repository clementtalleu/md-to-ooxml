<?php

declare(strict_types=1);

namespace Talleu\MdToOoxml\Tests\Unit\Renderer;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Talleu\MdToOoxml\Node\ParagraphNode;
use Talleu\MdToOoxml\Node\TextRunNode;
use Talleu\MdToOoxml\Renderer\NodeRenderer;
use Talleu\MdToOoxml\Renderer\TextRunRenderer;

class NodeRendererTest extends TestCase
{
    public function testThrowsOnUnregisteredNode(): void
    {
        $renderer = new NodeRenderer();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('No renderer registered');

        $renderer->render(new ParagraphNode());
    }

    public function testDispatchesToCorrectRenderer(): void
    {
        $renderer = new NodeRenderer();
        $renderer->addRenderer(TextRunNode::class, new TextRunRenderer());

        $xml = $renderer->render(new TextRunNode('test'));

        $this->assertStringContainsString('test', $xml);
    }
}
