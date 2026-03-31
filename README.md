# Markdown to OOXML

[![License: MIT](https://img.shields.io/badge/License-MIT-blue.svg)](LICENSE)

A lightweight, extensible PHP library to convert Markdown into **Office Open XML (OOXML)** — the XML format used inside Microsoft Word `.docx` files.

Use it to:

- **Generate `.docx` files** directly from Markdown (with zero dependency on PHPWord or LibreOffice)
- **Get raw OOXML strings** to embed into existing documents
- **Inject Markdown content** into `.docx` templates

---

## Features

| Feature | Built-in Parser | CommonMark Adapter |
| --- | --- | --- |
| Headings (H1–H6) with Word styles | ✅ | ✅ |
| Paragraphs & blank lines | ✅ | ✅ |
| **Bold**, *italic*, ***bold+italic*** | ✅ | ✅ |
| __Underline__ | ✅ | — |
| ~~Strikethrough~~ | ✅ | ✅ |
| `Inline code` | ✅ | ✅ |
| Fenced code blocks (with language) | ✅ | ✅ |
| Links | ✅ | ✅ |
| Images (text placeholder) | ✅ | ✅ |
| Bullet lists | ✅ | ✅ |
| Ordered lists | ✅ | ✅ |
| Blockquotes | ✅ | ✅ |
| Horizontal rules | ✅ | ✅ |
| Tables | ✅ | ✅ (requires table extension) |
| `.docx` file generation | ✅ | ✅ |
| Template injection | ✅ | ✅ |

---

## Requirements

- PHP **8.3+**
- `ext-zip` (only required for `.docx` generation via `DocxWriter`)

## Installation

```bash
composer require talleu/md-to-ooxml
```

### Optional: CommonMark support

```bash
composer require league/commonmark
```

---

## Quick Start

### 1. Markdown → OOXML string

```php
use Talleu\MdToOoxml\OoXmlConverterFactory;

$converter = OoXmlConverterFactory::create();

// Full document XML (document.xml content)
$xml = $converter->convert('# Hello **World**');

// Body fragment only (no XML declaration / envelope)
$bodyXml = $converter->convertToBodyXml('# Hello **World**');
```

### 2. Markdown → `.docx` file

```php
use Talleu\MdToOoxml\DocxWriter;

// One-liner: Markdown → .docx
DocxWriter::fromMarkdown('# My Document', '/path/to/output.docx');

// Or with a custom converter (e.g. CommonMark)
$converter = OoXmlConverterFactory::createWithCommonMark();
DocxWriter::fromMarkdown($markdown, '/path/to/output.docx', $converter);
```

### 3. Inject into an existing `.docx` template

```php
use Talleu\MdToOoxml\DocxWriter;
use Talleu\MdToOoxml\OoXmlConverterFactory;

$converter = OoXmlConverterFactory::create();
$bodyXml = $converter->convertToBodyXml('## New Section');

// Append content before </w:body>
DocxWriter::injectIntoTemplate(
    templatePath: '/path/to/template.docx',
    bodyXml: $bodyXml,
    outputPath: '/path/to/output.docx',
);

// Or replace a placeholder string in the template
DocxWriter::injectIntoTemplate(
    templatePath: '/path/to/template.docx',
    bodyXml: $bodyXml,
    outputPath: '/path/to/output.docx',
    placeholder: '{{CONTENT}}',
);
```

### 4. Two-step: convert then save manually

```php
use Talleu\MdToOoxml\OoXmlConverterFactory;
use Talleu\MdToOoxml\DocxWriter;

$converter = OoXmlConverterFactory::create();
$documentXml = $converter->convert($markdown);

// You can inspect/modify the XML here if needed
DocxWriter::save($documentXml, '/path/to/output.docx');
```

---

## Parsers

### Built-in Parser (zero dependencies)

The default parser handles all common Markdown syntax via regex-based parsing. It's fast, lightweight, and requires no extra packages.

```php
$converter = OoXmlConverterFactory::create();
```

> **Limitation:** The built-in parser does not support nested inline formatting. For example, `__some *italic* text__` will render the underline but treat the `*italic*` markers as literal text. If you need nested formatting (italic inside bold, underline inside strikethrough, etc.), use the CommonMark adapter below.

### League CommonMark Adapter

For advanced Markdown features, use the adapter for [`league/commonmark`](https://commonmark.thephpleague.com/):

```php
$converter = OoXmlConverterFactory::createWithCommonMark();
```

The adapter automatically enables the Table and Strikethrough extensions if available.

**Use the CommonMark adapter when you need:**

- Nested inline formatting (e.g. bold inside italic, italic inside underline)
- Strict [CommonMark](https://spec.commonmark.org/) compliance
- Edge cases the built-in regex parser may not handle correctly

### Custom Parser

Implement `MarkdownParserInterface` and inject it:

```php
use Talleu\MdToOoxml\OoXmlConverterFactory;

$converter = OoXmlConverterFactory::createWithParser(new MyCustomParser());
```

---

## Architecture

```
Markdown string
    │
    ▼
┌──────────────────┐
│  Parser           │  BlockParser (built-in) or LeagueCommonMarkAdapter
│  (Markdown → AST) │
└──────────────────┘
    │
    ▼
┌──────────────────┐
│  AST (Node tree)  │  DocumentNode → ParagraphNode → TextRunNode, etc.
└──────────────────┘
    │
    ▼
┌──────────────────┐
│  Renderer         │  NodeRenderer dispatches to per-node RendererInterface
│  (AST → OOXML)    │
└──────────────────┘
    │
    ▼
  OOXML string
    │
    ▼ (optional)
┌──────────────────┐
│  DocxWriter       │  Packages XML into a valid .docx ZIP archive
└──────────────────┘
```

### Node Types

| Node | Description |
| --- | --- |
| `DocumentNode` | Root node |
| `ParagraphNode` | Paragraph |
| `TitleNode` | Heading (level 1–6) |
| `TextRunNode` | Inline text with formatting flags |
| `InlineCodeNode` | Inline code span |
| `LinkNode` | Hyperlink |
| `ImageNode` | Image reference |
| `ListItemNode` | Bullet or ordered list item |
| `QuoteNode` | Blockquote |
| `CodeBlockNode` | Fenced code block |
| `BlankLineNode` | Empty line |
| `HorizontalRuleNode` | Horizontal rule / thematic break |
| `TableNode` | Table (contains `TableRowNode` → `TableCellNode`) |

### Extending

Register a custom renderer for any node type:

```php
use Talleu\MdToOoxml\Renderer\NodeRenderer;
use Talleu\MdToOoxml\Renderer\RendererInterface;
use Talleu\MdToOoxml\Node\NodeInterface;

class MyCustomRenderer implements RendererInterface
{
    public function render(NodeInterface $node): string
    {
        // Return OOXML string
    }
}

// Get the factory-built converter and add your renderer
$converter = OoXmlConverterFactory::create();
// Or build the NodeRenderer manually for full control
```

---

## Supported Markdown Syntax

```markdown
# Heading 1
## Heading 2
### Heading 3
#### Heading 4
##### Heading 5
###### Heading 6

Regular paragraph text.

**Bold text** and *italic text* and ***bold italic***.

__Underlined text__ and ~~strikethrough~~.

`inline code`

[Link text](https://example.com)

![Image alt](https://example.com/image.png)

- Bullet item
- Another item
* Also bullet
+ Also bullet

1. Ordered item
2. Another item

> Blockquote text

---

| Column A | Column B |
| -------- | -------- |
| Cell 1   | Cell 2   |

\```php
echo "fenced code block";
\```
```

---

## Testing

```bash
# Run all tests
vendor/bin/phpunit

# Run only unit tests
vendor/bin/phpunit --testsuite Unit

# Run only integration tests
vendor/bin/phpunit --testsuite Integration

# Run only functional tests (requires ext-zip)
vendor/bin/phpunit --testsuite Functional
```

---

## How It Works (OOXML Primer)

A `.docx` file is a ZIP archive containing XML files. The main one is `word/document.xml`. This library generates valid OOXML that follows the [ECMA-376](https://www.ecma-international.org/publications-and-standards/standards/ecma-376/) specification.

The generated `.docx` includes:

| File | Purpose |
| --- | --- |
| `[Content_Types].xml` | MIME type declarations |
| `_rels/.rels` | Package-level relationships |
| `word/document.xml` | The actual document content |
| `word/_rels/document.xml.rels` | Document-level relationships |
| `word/numbering.xml` | List (bullet/ordered) definitions |
| `word/styles.xml` | Heading and default styles |

---

## License

MIT — see [LICENSE](LICENSE).

