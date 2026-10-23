<?php

declare(strict_types=1);

namespace Irodev\LlmsTxt;

/**
 * Serves one of the files with sensible headers: UTF-8, a short cache, and noindex (they are guides for machines, not
 * pages for search results; robots.txt still lets crawlers fetch them).
 */
final class Response
{
    /** llms.txt and llms-full.txt: Markdown served as text/plain, as the proposal does. */
    public static function text(string $body, int $maxAge = 3600, bool $noindex = true): void
    {
        self::send($body, 'text/plain; charset=utf-8', $maxAge, $noindex);
    }

    /** AGENTS.md: text/markdown. */
    public static function markdown(string $body, int $maxAge = 3600, bool $noindex = true): void
    {
        self::send($body, 'text/markdown; charset=utf-8', $maxAge, $noindex);
    }

    /** @return array<string, string> the headers send() uses, for frameworks that set headers themselves */
    public static function headers(string $contentType, int $maxAge = 3600, bool $noindex = true): array
    {
        $headers = ['Content-Type' => $contentType, 'Cache-Control' => 'public, max-age=' . max(0, $maxAge), 'X-Content-Type-Options' => 'nosniff'];
        if ($noindex) {
            $headers['X-Robots-Tag'] = 'noindex';
        }
        return $headers;
    }

    private static function send(string $body, string $contentType, int $maxAge, bool $noindex): void
    {
        if (!headers_sent()) {
            foreach (self::headers($contentType, $maxAge, $noindex) as $name => $value) {
                header($name . ': ' . $value);
            }
        }
        echo $body;
    }
}
