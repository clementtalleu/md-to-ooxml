<?php

declare(strict_types=1);

namespace Talleu\MdToOoxml\Tests\Functional;

use PHPUnit\Framework\TestCase;
use RuntimeException;
use Talleu\MdToOoxml\DocxWriter;
use Talleu\MdToOoxml\OoXmlConverterFactory;
use ZipArchive;

/**
 * Functional tests: produce actual .docx files and verify their structure.
 *
 * These tests require the PHP zip extension.
 */
class DocxWriterTest extends TestCase
{
    private string $outputDir;

    protected function setUp(): void
    {
        if (!class_exists(ZipArchive::class)) {
            $this->markTestSkipped('PHP zip extension is required for .docx generation tests.');
        }

        $this->outputDir = sys_get_temp_dir() . '/md-to-ooxml-tests';

        if (!is_dir($this->outputDir)) {
            mkdir($this->outputDir, 0o755, true);
        }
    }

    protected function tearDown(): void
    {
        $files = glob($this->outputDir . '/*.docx') ?: [];

        foreach ($files as $file) {
            unlink($file);
        }

        if (is_dir($this->outputDir)) {
            @rmdir($this->outputDir);
        }
    }

    public function testFromMarkdownCreatesValidDocx(): void
    {
        $outputPath = $this->outputDir . '/test_basic.docx';

        DocxWriter::fromMarkdown('# Hello World', $outputPath);

        $this->assertFileExists($outputPath);
        $this->assertGreaterThan(0, filesize($outputPath));

        // Verify it's a valid ZIP/docx
        $zip = new ZipArchive();
        $this->assertTrue($zip->open($outputPath) === true);

        // Check required files exist
        $this->assertNotFalse($zip->getFromName('[Content_Types].xml'));
        $this->assertNotFalse($zip->getFromName('_rels/.rels'));
        $this->assertNotFalse($zip->getFromName('word/document.xml'));
        $this->assertNotFalse($zip->getFromName('word/_rels/document.xml.rels'));
        $this->assertNotFalse($zip->getFromName('word/numbering.xml'));
        $this->assertNotFalse($zip->getFromName('word/styles.xml'));

        // Verify document.xml content
        $documentXml = $zip->getFromName('word/document.xml');
        $this->assertIsString($documentXml);
        $this->assertStringContainsString('Hello World', $documentXml);
        $this->assertStringContainsString('Heading1', $documentXml);

        $zip->close();
    }

    public function testSaveWithCustomXml(): void
    {
        $outputPath = $this->outputDir . '/test_custom.docx';
        $converter = OoXmlConverterFactory::create();
        $xml = $converter->convert('A **bold** paragraph.');

        DocxWriter::save($xml, $outputPath);

        $this->assertFileExists($outputPath);

        $zip = new ZipArchive();
        $zip->open($outputPath);
        $documentXml = $zip->getFromName('word/document.xml');
        $this->assertIsString($documentXml);
        $this->assertStringContainsString('<w:b/>', $documentXml);
        $this->assertStringContainsString('bold', $documentXml);
        $zip->close();
    }

    public function testComprehensiveMarkdownToDocx(): void
    {
        $outputPath = $this->outputDir . '/test_comprehensive.docx';

        $markdown = <<<'MD'
            # Main Title

            This is a **bold** and *italic* paragraph with `inline code`.

            ## Second Level Heading

            A paragraph with a [link](https://example.com) and some ~~strikethrough~~ text.

            ### Lists

            Bullet list:
            - First item
            - Second item with **bold**
            - Third item

            Ordered list:
            1. Step one
            2. Step two
            3. Step three

            ### Blockquote

            > This is a blockquote with some *emphasis*.

            ### Code Block

            ```php
            <?php
            echo "Hello, World!";
            $x = 1 + 2;
            ```

            ### Table

            | Feature | Supported |
            | --- | --- |
            | Bold | Yes |
            | Italic | Yes |
            | Tables | Yes |

            ---

            #### Heading 4

            ##### Heading 5

            ###### Heading 6

            Final paragraph with __underline__ formatting.
            MD;

        DocxWriter::fromMarkdown($markdown, $outputPath);

        $this->assertFileExists($outputPath);

        $zip = new ZipArchive();
        $zip->open($outputPath);
        $documentXml = $zip->getFromName('word/document.xml');
        $this->assertIsString($documentXml);

        // Verify all major elements are present
        $this->assertStringContainsString('Main Title', $documentXml);
        $this->assertStringContainsString('Heading1', $documentXml);
        $this->assertStringContainsString('Heading2', $documentXml);
        $this->assertStringContainsString('Heading3', $documentXml);
        $this->assertStringContainsString('<w:b/>', $documentXml);
        $this->assertStringContainsString('<w:i/>', $documentXml);
        $this->assertStringContainsString('<w:strike/>', $documentXml);
        $this->assertStringContainsString('HYPERLINK', $documentXml);
        $this->assertStringContainsString('Courier New', $documentXml);
        $this->assertStringContainsString('<w:numId', $documentXml);
        $this->assertStringContainsString('<w:ind w:left="720"/>', $documentXml);
        $this->assertStringContainsString('<w:tbl>', $documentXml);
        $this->assertStringContainsString('<w:pBdr>', $documentXml);

        // Verify numbering.xml has both list definitions
        $numberingXml = $zip->getFromName('word/numbering.xml');
        $this->assertIsString($numberingXml);
        $this->assertStringContainsString('w:numFmt w:val="bullet"', $numberingXml);
        $this->assertStringContainsString('w:numFmt w:val="decimal"', $numberingXml);

        // Verify styles.xml has heading definitions
        $stylesXml = $zip->getFromName('word/styles.xml');
        $this->assertIsString($stylesXml);
        $this->assertStringContainsString('Heading1', $stylesXml);
        $this->assertStringContainsString('Heading6', $stylesXml);

        $zip->close();
    }

    public function testInjectIntoTemplate(): void
    {
        // First create a "template" docx
        $templatePath = $this->outputDir . '/template.docx';
        $outputPath = $this->outputDir . '/injected.docx';

        DocxWriter::fromMarkdown('# Template Content', $templatePath);

        // Now inject additional content
        $converter = OoXmlConverterFactory::create();
        $bodyXml = $converter->convertToBodyXml('## Injected Section');

        DocxWriter::injectIntoTemplate($templatePath, $bodyXml, $outputPath);

        $this->assertFileExists($outputPath);

        $zip = new ZipArchive();
        $zip->open($outputPath);
        $documentXml = $zip->getFromName('word/document.xml');
        $this->assertIsString($documentXml);

        $this->assertStringContainsString('Template Content', $documentXml);
        $this->assertStringContainsString('Injected Section', $documentXml);

        $zip->close();

        // Clean up the extra template file
        unlink($templatePath);
    }

    public function testInjectWithPlaceholder(): void
    {
        $templatePath = $this->outputDir . '/template_placeholder.docx';
        $outputPath = $this->outputDir . '/injected_placeholder.docx';

        // Build a template manually with a known placeholder XML block
        $placeholder = '<!-- INJECT_HERE -->';
        $documentXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<w:document xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main">'
            . '<w:body>'
            . '<w:p><w:r><w:t>Before</w:t></w:r></w:p>'
            . $placeholder
            . '<w:p><w:r><w:t>After</w:t></w:r></w:p>'
            . '</w:body></w:document>';

        DocxWriter::save($documentXml, $templatePath);

        // Inject replacing the placeholder
        $bodyXml = '<w:p><w:r><w:t>Replaced Content</w:t></w:r></w:p>';
        DocxWriter::injectIntoTemplate($templatePath, $bodyXml, $outputPath, $placeholder);

        $zip = new ZipArchive();
        $zip->open($outputPath);
        $result = $zip->getFromName('word/document.xml');
        $this->assertIsString($result);

        $this->assertStringContainsString('Replaced Content', $result);
        $this->assertStringContainsString('Before', $result);
        $this->assertStringContainsString('After', $result);
        $this->assertStringNotContainsString($placeholder, $result);

        $zip->close();

        unlink($templatePath);
    }

    public function testInjectIntoNonExistentTemplateThrows(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Template file not found');

        DocxWriter::injectIntoTemplate('/nonexistent/template.docx', '<w:p/>', '/tmp/out.docx');
    }
}
