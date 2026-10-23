<?php

declare(strict_types=1);

namespace Irodev\LlmsTxt;

/**
 * Checks an llms.txt against the proposal (https://llmstxt.org). Errors break the format; warnings are departures a
 * reader can still cope with.
 */
final class Validator
{
    /** @return array{errors: list<string>, warnings: list<string>} */
    public static function check(string $text): array
    {
        $errors = [];
        $warnings = [];
        $lines = preg_split('/\R/u', $text) ?: [];
        $firstLine = 0;
        while ($firstLine < count($lines) && trim($lines[$firstLine]) === '') {
            $firstLine++;
        }
        if (!isset($lines[$firstLine]) || !preg_match('/^#\s+\S/u', $lines[$firstLine])) {
            $errors[] = 'The file must start with an H1: "# Name of the site or project".';
        }
        if (preg_match_all('/^#\s/mu', $text) > 1) {
            $errors[] = 'Only one H1 is allowed; use H2 ("## …") for sections.';
        }
        if (stripos($text, '<html') !== false || stripos($text, '<body') !== false) {
            $errors[] = 'This is HTML; llms.txt is Markdown served as plain text.';
        }
        if (!preg_match('/^>\s+\S/mu', $text)) {
            $warnings[] = 'No summary: add a blockquote ("> …") under the title with the key facts.';
        }
        if (preg_match('/^#{3,}\s/mu', $text)) {
            $warnings[] = 'Headings below H2 are not part of the proposal\'s structure.';
        }

        $section = null;
        $sections = [];
        foreach ($lines as $i => $line) {
            if (preg_match('/^##\s+(.+)$/u', $line, $m)) {
                $section = trim($m[1]);
                $sections[$section] = 0;
                continue;
            }
            if ($section === null || trim($line) === '') {
                continue;
            }
            $n = $i + 1;
            if (!preg_match('/^[-*]\s+\[((?:\\\\.|[^\]\\\\])+)\]\(([^)\s]+)\)(?::\s*(.*))?$/u', trim($line), $link)) {
                $warnings[] = "Line $n: sections should hold list items that are links: \"- [Title](https://…): notes\".";
                continue;
            }
            $sections[$section]++;
            $url = $link[2];
            if (!preg_match('~^https?://~i', $url)) {
                $warnings[] = "Line $n: use an absolute URL ($url) so the link works outside your site.";
            } elseif (str_starts_with(strtolower($url), 'http://')) {
                $warnings[] = "Line $n: the link uses http:// ($url); use https:// if your site has it.";
            }
        }
        foreach ($sections as $name => $count) {
            if ($count === 0) {
                $warnings[] = "Section \"$name\" has no links.";
            }
        }
        if ($sections === []) {
            $warnings[] = 'No sections: add at least one "## Section" with links to your most useful pages.';
        }
        return ['errors' => $errors, 'warnings' => $warnings];
    }

    public static function isValid(string $text): bool
    {
        return self::check($text)['errors'] === [];
    }
}
