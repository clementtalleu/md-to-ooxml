<?php

declare(strict_types=1);

namespace Talleu\MdToOoxml\Tests\Unit\Renderer;

use PHPUnit\Framework\TestCase;
use Talleu\MdToOoxml\Node\DocumentNode;
use Talleu\MdToOoxml\Node\ParagraphNode;
use Talleu\MdToOoxml\Node\TextRunNode;
use Talleu\MdToOoxml\Renderer\DocumentRenderer;
use Talleu\MdToOoxml\Renderer\NodeRenderer;
use Talleu\MdToOoxml\Renderer\ParagraphRenderer;
use Talleu\MdToOoxml\Renderer\TextRunRenderer;

class DocumentRendererTest extends TestCase
{
    public function testRenderDocument(): void
    {
        $nodeRenderer = new NodeRenderer();
        $nodeRenderer->addRenderer(TextRunNode::class, new TextRunRenderer());
        $nodeRenderer->addRenderer(ParagraphNode::class, new ParagraphRenderer($nodeRenderer));
        $nodeRenderer->addRenderer(DocumentNode::class, new DocumentRenderer($nodeRenderer));

        $doc = new DocumentNode();
        $p = new ParagraphNode();
        $p->addChild(new TextRunNode('Hello'));
        $doc->addChild($p);

        $xml = $nodeRenderer->render($doc);

        $this->assertStringStartsWith('<?xml version="1.0" encoding="UTF-8" standalone="yes"?>', $xml);
        $this->assertStringContainsString('<w:document', $xml);
        $this->assertStringContainsString('<w:body>', $xml);
        $this->assertStringContainsString('</w:body>', $xml);
        $this->assertStringContainsString('Hello', $xml);
    }

    public function testDocumentContainsNamespaces(): void
    {
        $nodeRenderer = new NodeRenderer();
        $nodeRenderer->addRenderer(DocumentNode::class, new DocumentRenderer($nodeRenderer));

        $doc = new DocumentNode();
        $xml = $nodeRenderer->render($doc);

        $this->assertStringContainsString('xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main"', $xml);
        $this->assertStringContainsString('xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"', $xml);
    }
}
