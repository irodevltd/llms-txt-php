<?php

declare(strict_types=1);

namespace Irodev\LlmsTxt;

/**
 * Builds /llms.txt as the proposal at https://llmstxt.org describes it:
 *
 *   # Site name
 *
 *   > One-paragraph summary
 *
 *   Optional details: paragraphs, no headings.
 *
 *   ## Section
 *
 *   - [Page title](https://…): what the reader finds there
 *
 *   ## Optional
 *
 *   - [Less important page](https://…)
 *
 * The "Optional" section is the one a tool may skip when it needs a shorter context.
 */
final class LlmsTxt implements \Stringable
{
    private string $summary = '';

    /** @var list<string> */
    private array $details = [];

    /** @var array<string, list<array{string, string, string}>> section => [title, url, notes] */
    private array $sections = [];

    private ?string $current = null;

    public function __construct(private readonly string $title)
    {
        if (Markdown::line($title) === '') {
            throw new \InvalidArgumentException('llms.txt needs a title: the name of the site or project.');
        }
    }

    public static function create(string $title): self
    {
        return new self($title);
    }

    /** The short summary shown as a blockquote under the title: the key facts a reader needs first. */
    public function summary(string $text): self
    {
        $this->summary = Markdown::line($text);
        return $this;
    }

    /** A paragraph of detail after the summary (company facts, languages, how to use the links). */
    public function details(string $paragraph): self
    {
        $p = trim($paragraph);
        if ($p !== '') {
            $this->details[] = $p;
        }
        return $this;
    }

    /** Start a section (an H2); the links that follow go into it. Calling it again with the same name continues it. */
    public function section(string $name): self
    {
        $name = Markdown::line($name);
        $this->sections[$name] ??= [];
        $this->current = $name;
        return $this;
    }

    /** Start the "Optional" section: links a tool may leave out when it needs a shorter context. */
    public function optional(): self
    {
        return $this->section('Optional');
    }

    /** Add a link to the current section. $notes says what the reader finds there. */
    public function link(string $title, string $url, string $notes = ''): self
    {
        if ($this->current === null) {
            throw new \LogicException('Call section() before link(): every link belongs to a section.');
        }
        $this->sections[$this->current][] = [$title, $url, $notes];
        return $this;
    }

    public function render(): string
    {
        $out = ['# ' . Markdown::line($this->title), ''];
        if ($this->summary !== '') {
            array_push($out, '> ' . $this->summary, '');
        }
        foreach ($this->details as $p) {
            array_push($out, $p, '');
        }
        $sections = $this->sections;
        if (isset($sections['Optional'])) {                          // the proposal expects it last
            $optional = $sections['Optional'];
            unset($sections['Optional']);
            $sections['Optional'] = $optional;
        }
        foreach ($sections as $name => $links) {
            if ($links === []) {
                continue;
            }
            array_push($out, '## ' . $name, '');
            foreach ($links as [$title, $url, $notes]) {
                $notes = Markdown::line($notes);
                $out[] = '- [' . Markdown::linkText($title) . '](' . Markdown::url($url) . ')' . ($notes !== '' ? ': ' . $notes : '');
            }
            $out[] = '';
        }
        return implode("\n", $out);
    }

    public function __toString(): string
    {
        return $this->render();
    }
}
