<?php

declare(strict_types=1);

namespace Irodev\LlmsTxt;

/** The few Markdown rules the builders need: one-line text, safe link text, plain text out of HTML. */
final class Markdown
{
    /** Collapse whitespace and line breaks: a list item or a heading must stay on one line. */
    public static function line(string $s): string
    {
        return trim((string) preg_replace('/\s+/u', ' ', $s));
    }

    /** Text for inside [link text]: square brackets escaped. */
    public static function linkText(string $s): string
    {
        return strtr(self::line($s), ['\\' => '\\\\', '[' => '\\[', ']' => '\\]']);
    }

    /** A URL for inside (…): spaces and parentheses encoded so the link can't break. */
    public static function url(string $url): string
    {
        return strtr(trim($url), [' ' => '%20', '(' => '%28', ')' => '%29']);
    }

    /** Plain text from a snippet of HTML (your CMS fields often have some): tags dropped, entities decoded. */
    public static function plain(string $html): string
    {
        return self::line(html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
    }
}
