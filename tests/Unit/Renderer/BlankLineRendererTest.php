<?php

declare(strict_types=1);

namespace Talleu\MdToOoxml\Tests\Unit\Renderer;

use PHPUnit\Framework\TestCase;
use Talleu\MdToOoxml\Node\BlankLineNode;
use Talleu\MdToOoxml\Renderer\BlankLineRenderer;

class BlankLineRendererTest extends TestCase
{
    public function testRenderBlankLine(): void
    {
        $renderer = new BlankLineRenderer();
        $xml = $renderer->render(new BlankLineNode());

        $this->assertSame('<w:p/>', $xml);
    }
}
