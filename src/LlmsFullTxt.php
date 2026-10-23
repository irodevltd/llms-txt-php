<?php

declare(strict_types=1);

namespace Irodev\LlmsTxt;

/**
 * Builds /llms-full.txt: the llms.txt index followed by the full content of the pages it points to, one document after
 * another, so a tool can read the whole site in one request. There is no formal spec for this file; this is the
 * common shape: each document starts after a horizontal rule with its title as an H1 and its address on the next line.
 */
final class LlmsFullTxt implements \Stringable
{
    /** @var list<array{string, string, string, string}> [title, url, markdown, date] */
    private array $documents = [];

    public function __construct(private readonly LlmsTxt $index)
    {
    }

    /**
     * Add one page in full.
     *
     * @param string $markdown the page's content as Markdown or plain text (not HTML: run it through your own converter)
     * @param string $date     optional, e.g. the date it was published or last changed (2026-10-23)
     */
    public function document(string $title, string $url, string $markdown, string $date = ''): self
    {
        $this->documents[] = [$title, $url, trim($markdown), trim($date)];
        return $this;
    }

    public function render(): string
    {
        $out = [rtrim($this->index->render())];
        foreach ($this->documents as [$title, $url, $markdown, $date]) {
            array_push($out, '', '---', '', '# ' . Markdown::line($title), Markdown::url($url) . ($date !== '' ? ' · ' . $date : ''), '', $markdown);
        }
        return implode("\n", $out) . "\n";
    }

    public function __toString(): string
    {
        return $this->render();
    }
}
