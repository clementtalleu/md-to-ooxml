<?php

declare(strict_types=1);

namespace Talleu\MdToOoxml\Tests\Unit\Renderer;

use PHPUnit\Framework\TestCase;
use Talleu\MdToOoxml\Node\HorizontalRuleNode;
use Talleu\MdToOoxml\Renderer\HorizontalRuleRenderer;

class HorizontalRuleRendererTest extends TestCase
{
    public function testRenderHorizontalRule(): void
    {
        $renderer = new HorizontalRuleRenderer();
        $xml = $renderer->render(new HorizontalRuleNode());

        $this->assertStringContainsString('<w:pBdr>', $xml);
        $this->assertStringContainsString('<w:bottom', $xml);
        $this->assertStringStartsWith('<w:p>', $xml);
    }
}
