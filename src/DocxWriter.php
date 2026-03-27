<?php

declare(strict_types=1);

namespace Talleu\MdToOoxml;

use RuntimeException;
use ZipArchive;

/**
 * Generates a valid .docx file (Office Open XML package) from OOXML content.
 *
 * A .docx file is a ZIP archive containing at minimum:
 *   - [Content_Types].xml
 *   - _rels/.rels
 *   - word/document.xml
 *   - word/_rels/document.xml.rels
 *   - word/numbering.xml (for list support)
 *   - word/styles.xml (for heading styles)
 *
 * This writer can also inject OOXML body fragments into an existing .docx template.
 *
 * Requires the PHP `zip` extension.
 */
class DocxWriter
{
    /**
     * Create a .docx file from a full OOXML document string.
     *
     * @param string $documentXml The full document.xml content (with XML declaration and <w:document> envelope)
     * @param string $outputPath  Path where the .docx file will be written
     */
    public static function save(string $documentXml, string $outputPath): void
    {
        self::ensureZipExtension();

        $zip = new ZipArchive();

        if ($zip->open($outputPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new RuntimeException(sprintf('Cannot create .docx file at "%s".', $outputPath));
        }

        $zip->addFromString('[Content_Types].xml', self::contentTypesXml());
        $zip->addFromString('_rels/.rels', self::relsXml());
        $zip->addFromString('word/document.xml', $documentXml);
        $zip->addFromString('word/_rels/document.xml.rels', self::documentRelsXml());
        $zip->addFromString('word/numbering.xml', self::numberingXml());
        $zip->addFromString('word/styles.xml', self::stylesXml());

        $zip->close();
    }

    /**
     * Create a .docx file directly from Markdown using the converter.
     */
    public static function fromMarkdown(string $markdown, string $outputPath, ?OoXmlConverter $converter = null): void
    {
        $converter ??= OoXmlConverterFactory::create();
        $documentXml = $converter->convert($markdown);

        self::save($documentXml, $outputPath);
    }

    /**
     * Inject OOXML body content into an existing .docx template.
     *
     * Replaces a placeholder string in the template's document.xml.
     * If no placeholder is given, appends the content before the closing </w:body> tag.
     *
     * @param string      $templatePath Path to the existing .docx template
     * @param string      $bodyXml      The OOXML body fragment to inject
     * @param string      $outputPath   Path where the modified .docx will be written
     * @param string|null $placeholder  Optional placeholder string to replace in the template
     */
    public static function injectIntoTemplate(
        string $templatePath,
        string $bodyXml,
        string $outputPath,
        ?string $placeholder = null,
    ): void {
        self::ensureZipExtension();

        if (!file_exists($templatePath)) {
            throw new RuntimeException(sprintf('Template file not found: "%s".', $templatePath));
        }

        // Copy the template to the output path
        if (!copy($templatePath, $outputPath)) {
            throw new RuntimeException(sprintf('Cannot copy template to "%s".', $outputPath));
        }

        $zip = new ZipArchive();

        if ($zip->open($outputPath) !== true) {
            throw new RuntimeException(sprintf('Cannot open .docx file at "%s".', $outputPath));
        }

        $documentXml = $zip->getFromName('word/document.xml');

        if ($documentXml === false) {
            $zip->close();

            throw new RuntimeException('The template does not contain a word/document.xml file.');
        }

        if ($placeholder !== null) {
            $documentXml = str_replace($placeholder, $bodyXml, $documentXml);
        } else {
            // Append before </w:body>
            $documentXml = str_replace('</w:body>', $bodyXml . '</w:body>', $documentXml);
        }

        $zip->addFromString('word/document.xml', $documentXml);
        $zip->close();
    }

    private static function ensureZipExtension(): void
    {
        if (!class_exists(ZipArchive::class)) {
            throw new RuntimeException(
                'The PHP zip extension is required to generate .docx files. '
                . 'Install it with: apt-get install php-zip (Linux) or enable it in php.ini.',
            );
        }
    }

    private static function contentTypesXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
            . '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
            . '<Default Extension="xml" ContentType="application/xml"/>'
            . '<Override PartName="/word/document.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.document.main+xml"/>'
            . '<Override PartName="/word/numbering.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.numbering+xml"/>'
            . '<Override PartName="/word/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.styles+xml"/>'
            . '</Types>';
    }

    private static function relsXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="word/document.xml"/>'
            . '</Relationships>';
    }

    private static function documentRelsXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/numbering" Target="numbering.xml"/>'
            . '<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>'
            . '</Relationships>';
    }

    /**
     * Numbering definitions for bullet and ordered lists.
     */
    private static function numberingXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<w:numbering xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main">'
            // Abstract numbering 1: bullet list
            . '<w:abstractNum w:abstractNumId="1">'
            . '<w:lvl w:ilvl="0"><w:start w:val="1"/><w:numFmt w:val="bullet"/><w:lvlText w:val="&#x2022;"/><w:lvlJc w:val="left"/>'
            . '<w:pPr><w:ind w:left="720" w:hanging="360"/></w:pPr>'
            . '<w:rPr><w:rFonts w:ascii="Symbol" w:hAnsi="Symbol" w:hint="default"/></w:rPr>'
            . '</w:lvl>'
            . '</w:abstractNum>'
            // Abstract numbering 2: ordered (decimal) list
            . '<w:abstractNum w:abstractNumId="2">'
            . '<w:lvl w:ilvl="0"><w:start w:val="1"/><w:numFmt w:val="decimal"/><w:lvlText w:val="%1."/><w:lvlJc w:val="left"/>'
            . '<w:pPr><w:ind w:left="720" w:hanging="360"/></w:pPr>'
            . '</w:lvl>'
            . '</w:abstractNum>'
            // Concrete numbering instances
            . '<w:num w:numId="1"><w:abstractNumId w:val="1"/></w:num>'
            . '<w:num w:numId="2"><w:abstractNumId w:val="2"/></w:num>'
            . '</w:numbering>';
    }

    /**
     * Minimal styles.xml with heading style definitions.
     */
    private static function stylesXml(): string
    {
        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<w:styles xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main">'
            . '<w:style w:type="paragraph" w:default="1" w:styleId="Normal">'
            . '<w:name w:val="Normal"/>'
            . '<w:rPr><w:sz w:val="22"/><w:szCs w:val="22"/><w:rFonts w:ascii="Calibri" w:hAnsi="Calibri"/></w:rPr>'
            . '</w:style>';

        $headingSizes = [48, 36, 28, 24, 22, 20];

        for ($i = 1; $i <= 6; $i++) {
            $size = $headingSizes[$i - 1];
            $xml .= '<w:style w:type="paragraph" w:styleId="Heading' . $i . '">'
                . '<w:name w:val="heading ' . $i . '"/>'
                . '<w:basedOn w:val="Normal"/>'
                . '<w:next w:val="Normal"/>'
                . '<w:pPr><w:keepNext/><w:keepLines/><w:spacing w:before="240" w:after="120"/></w:pPr>'
                . '<w:rPr><w:b/><w:sz w:val="' . $size . '"/><w:szCs w:val="' . $size . '"/></w:rPr>'
                . '</w:style>';
        }

        $xml .= '</w:styles>';

        return $xml;
    }
}
