<?php

declare(strict_types=1);

namespace Talleu\MdToOoxml\Parser;

use Talleu\MdToOoxml\Node\ImageNode;
use Talleu\MdToOoxml\Node\InlineCodeNode;
use Talleu\MdToOoxml\Node\LinkNode;
use Talleu\MdToOoxml\Node\NodeInterface;
use Talleu\MdToOoxml\Node\TextRunNode;

class InlineParser
{
    /**
     * Parse inline Markdown formatting and return an array of inline nodes.
     *
     * Supported: bold, italic, bold+italic, underline, strikethrough,
     *            inline code, links, and images.
     *
     * @return NodeInterface[]
     */
    public function parse(string $text): array
    {
        $nodes = [];

        // Order matters: most specific patterns first to avoid partial matches.
        $pattern = '/('
            . '!\[.*?]\(.*?\)'          // images
            . '|\[.*?]\(.*?\)'          // links
            . '|`[^`]+`'                // inline code
            . '|\*\*\*.*?\*\*\*'        // bold+italic (*** ***)
            . '|\*\*.*?\*\*'            // bold
            . '|~~.*?~~'                // strikethrough
            . '|\*.*?\*'               // italic (*)
            . '|__.*?__'               // underline
            . '|_.*?_'                 // italic (_)
            . ')/s';

        $parts = preg_split($pattern, $text, -1, PREG_SPLIT_DELIM_CAPTURE);

        if ($parts === false) {
            return [new TextRunNode($text)];
        }

        foreach ($parts as $part) {
            if ($part === '') {
                continue;
            }

            // Image: ![alt](url)
            if (preg_match('/^!\[(.*?)]\((.*?)\)$/', $part, $m)) {
                $nodes[] = new ImageNode($m[1], $m[2]);
            }
            // Link: [text](url)
            elseif (preg_match('/^\[(.*?)]\((.*?)\)$/', $part, $m)) {
                $nodes[] = new LinkNode($m[1], $m[2]);
            }
            // Inline code: `code`
            elseif (preg_match('/^`([^`]+)`$/', $part, $m)) {
                $nodes[] = new InlineCodeNode($m[1]);
            }
            // Bold + Italic: ***text***
            elseif (preg_match('/^\*\*\*(.*?)\*\*\*$/s', $part, $m)) {
                $nodes[] = new TextRunNode($m[1], isBold: true, isItalic: true);
            }
            // Bold: **text**
            elseif (preg_match('/^\*\*(.*?)\*\*$/s', $part, $m)) {
                $nodes[] = new TextRunNode($m[1], isBold: true);
            }
            // Strikethrough: ~~text~~
            elseif (preg_match('/^~~(.*?)~~$/s', $part, $m)) {
                $nodes[] = new TextRunNode($m[1], isStrikethrough: true);
            }
            // Italic: *text*
            elseif (preg_match('/^\*(.*?)\*$/s', $part, $m)) {
                $nodes[] = new TextRunNode($m[1], isItalic: true);
            }
            // Underline: __text__
            elseif (preg_match('/^__(.*?)__$/s', $part, $m)) {
                $nodes[] = new TextRunNode($m[1], isUnderline: true);
            }
            // Italic (underscore): _text_
            elseif (preg_match('/^_(.*?)_$/s', $part, $m)) {
                $nodes[] = new TextRunNode($m[1], isItalic: true);
            }
            // Plain text
            else {
                $nodes[] = new TextRunNode($part);
            }
        }

        return $nodes;
    }
}
