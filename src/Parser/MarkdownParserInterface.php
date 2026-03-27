<?php

declare(strict_types=1);

namespace Talleu\MdToOoxml\Parser;

use Talleu\MdToOoxml\Node\DocumentNode;

interface MarkdownParserInterface
{
    /**
     * Parse a Markdown string and return the AST as a DocumentNode.
     */
    public function parse(string $markdown): DocumentNode;
}
