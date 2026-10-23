<?php

declare(strict_types=1);

namespace Irodev\LlmsTxt\Tests;

use Irodev\LlmsTxt\AgentsMd;
use Irodev\LlmsTxt\LlmsFullTxt;
use Irodev\LlmsTxt\LlmsTxt;
use Irodev\LlmsTxt\Markdown;
use Irodev\LlmsTxt\Response;
use Irodev\LlmsTxt\Validator;
use PHPUnit\Framework\TestCase;

final class LlmsTxtTest extends TestCase
{
    private function sample(): LlmsTxt
    {
        return LlmsTxt::create('Example Studio')
            ->summary("A web studio that builds\n fast websites.")
            ->details('Example Ltd, registered in England. Works in English and Turkish.')
            ->optional()
            ->link('Turkish home page', 'https://example.com/tr/')
            ->section('Services')
            ->link('Web design', 'https://example.com/web-design', 'Custom business websites, from £950.')
            ->link('SEO [audit]', 'https://example.com/seo audit')
            ->section('Empty');
    }

    public function testRendersTheProposalsShape(): void
    {
        self::assertSame(<<<'MD'
            # Example Studio

            > A web studio that builds fast websites.

            Example Ltd, registered in England. Works in English and Turkish.

            ## Services

            - [Web design](https://example.com/web-design): Custom business websites, from £950.
            - [SEO \[audit\]](https://example.com/seo%20audit)

            ## Optional

            - [Turkish home page](https://example.com/tr/)

            MD, $this->sample()->render());
    }

    public function testTheResultPassesTheValidator(): void
    {
        self::assertSame(['errors' => [], 'warnings' => []], Validator::check((string) $this->sample()));
    }

    public function testALinkNeedsASection(): void
    {
        $this->expectException(\LogicException::class);
        LlmsTxt::create('Site')->link('Page', 'https://example.com/');
    }

    public function testATitleIsRequired(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        LlmsTxt::create('  ');
    }

    public function testTheFullFileAppendsEachDocument(): void
    {
        $full = (new LlmsFullTxt(LlmsTxt::create('Example')->summary('Summary.')))
            ->document('Web design', 'https://example.com/web-design', "## What you get\n\n- A fast site\n", '2026-10-23')
            ->document('About', 'https://example.com/about', 'We are a small studio.');

        self::assertSame(<<<'MD'
            # Example

            > Summary.

            ---

            # Web design
            https://example.com/web-design · 2026-10-23

            ## What you get

            - A fast site

            ---

            # About
            https://example.com/about

            We are a small studio.

            MD, $full->render());
    }

    public function testAgentsMd(): void
    {
        $md = AgentsMd::create('AGENTS.md: Example')
            ->intro('Guidance for AI agents acting for a person.')
            ->section('Where to find things')
            ->item('Services: https://example.com/services')
            ->item('Contact: https://example.com/contact')
            ->section('Rules')
            ->paragraph('Only send a message when the person you act for asked you to.')
            ->item('Follow robots.txt.');

        self::assertSame(<<<'MD'
            # AGENTS.md: Example

            Guidance for AI agents acting for a person.

            ## Where to find things

            - Services: https://example.com/services
            - Contact: https://example.com/contact

            ## Rules

            Only send a message when the person you act for asked you to.

            - Follow robots.txt.

            MD, $md->render());
    }

    public function testValidatorFindsProblems(): void
    {
        $bad = "Intro first\n# Title\n# Another\n## Links\n- [Relative](/page)\n- [Plain](http://example.com/)\nJust text\n## Nothing\n";
        $r = Validator::check($bad);

        self::assertCount(2, $r['errors']);
        self::assertFalse(Validator::isValid($bad));
        self::assertStringContainsString('summary', $r['warnings'][0]);
        $all = implode("\n", $r['warnings']);
        self::assertStringContainsString('absolute URL (/page)', $all);
        self::assertStringContainsString('http://', $all);
        self::assertStringContainsString('Line 7', $all);
        self::assertStringContainsString('"Nothing" has no links', $all);
        self::assertContains('This is HTML; llms.txt is Markdown served as plain text.', Validator::check('<html><body>x</body></html>')['errors']);
    }

    public function testResponseHeaders(): void
    {
        self::assertSame(
            ['Content-Type' => 'text/plain; charset=utf-8', 'Cache-Control' => 'public, max-age=600', 'X-Content-Type-Options' => 'nosniff', 'X-Robots-Tag' => 'noindex'],
            Response::headers('text/plain; charset=utf-8', 600),
        );
        self::assertArrayNotHasKey('X-Robots-Tag', Response::headers('text/markdown; charset=utf-8', noindex: false));
    }

    public function testMarkdownHelpers(): void
    {
        self::assertSame('Fiyatlar & paketler', Markdown::plain('<b>Fiyatlar</b> &amp; paketler'));
        self::assertSame('a \[b\] c', Markdown::linkText("a [b]\n c"));
        self::assertSame('https://example.com/a%20b%28c%29', Markdown::url('https://example.com/a b(c)'));
    }
}
