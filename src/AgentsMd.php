<?php

declare(strict_types=1);

namespace Irodev\LlmsTxt;

/**
 * Builds an AGENTS.md for a website: plain guidance for AI agents and assistants that read or use the site on behalf
 * of a person (what the site is, where things are, how to ask for something, what not to do). AGENTS.md started as a
 * convention for code repositories; on a website it is simply a Markdown page written for agents.
 */
final class AgentsMd implements \Stringable
{
    private string $intro = '';

    /** @var array<string, list<array{string, string}>> section => [kind ('p'|'li'), text] */
    private array $sections = [];

    private ?string $current = null;

    public function __construct(private readonly string $title)
    {
    }

    public static function create(string $title): self
    {
        return new self($title);
    }

    public function intro(string $text): self
    {
        $this->intro = trim($text);
        return $this;
    }

    /** Start a section (an H2). */
    public function section(string $name): self
    {
        $name = Markdown::line($name);
        $this->sections[$name] ??= [];
        $this->current = $name;
        return $this;
    }

    /** A paragraph in the current section. */
    public function paragraph(string $text): self
    {
        return $this->add('p', trim($text));
    }

    /** A bullet in the current section. */
    public function item(string $text): self
    {
        return $this->add('li', Markdown::line($text));
    }

    public function render(): string
    {
        $out = ['# ' . Markdown::line($this->title), ''];
        if ($this->intro !== '') {
            array_push($out, $this->intro, '');
        }
        foreach ($this->sections as $name => $blocks) {
            array_push($out, '## ' . $name, '');
            $previous = null;
            foreach ($blocks as [$kind, $text]) {
                if ($previous !== null && ($kind === 'p' || $previous === 'p')) {
                    $out[] = '';
                }
                $out[] = $kind === 'li' ? '- ' . $text : $text;
                $previous = $kind;
            }
            $out[] = '';
        }
        return implode("\n", $out);
    }

    public function __toString(): string
    {
        return $this->render();
    }

    private function add(string $kind, string $text): self
    {
        if ($this->current === null) {
            throw new \LogicException('Call section() first.');
        }
        if ($text !== '') {
            $this->sections[$this->current][] = [$kind, $text];
        }
        return $this;
    }
}
