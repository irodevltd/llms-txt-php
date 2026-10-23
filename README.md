# llms.txt for PHP

[![tests](https://github.com/irodevltd/llms-txt-php/actions/workflows/tests.yml/badge.svg)](https://github.com/irodevltd/llms-txt-php/actions/workflows/tests.yml)
![PHP](https://img.shields.io/badge/php-%3E%3D8.1-777bb4)
![License](https://img.shields.io/badge/license-MIT-blue)

**English** · [Türkçe](README.tr.md)

Build `/llms.txt`, `/llms-full.txt` and `/AGENTS.md` for your site **from your own data**, so they never drift from
your pages. Check any llms.txt against [the proposal](https://llmstxt.org), and serve the files with the right
headers. No dependencies.

```php
use Irodev\LlmsTxt\LlmsTxt;

echo LlmsTxt::create('Example Studio')
    ->summary('Example Studio builds websites and online stores for small businesses.')
    ->details('Example Ltd, registered in England and Wales. Email: hello@example.com.')
    ->section('Services')
    ->link('Corporate websites', 'https://example.com/services/websites', 'Custom business websites that load fast on phones.')
    ->link('Online stores', 'https://example.com/services/stores', 'Shopify or built to order.')
    ->optional()
    ->link('Full text for AI tools', 'https://example.com/llms-full.txt');
```

```markdown
# Example Studio

> Example Studio builds websites and online stores for small businesses.

Example Ltd, registered in England and Wales. Email: hello@example.com.

## Services

- [Corporate websites](https://example.com/services/websites): Custom business websites that load fast on phones.
- [Online stores](https://example.com/services/stores): Shopify or built to order.

## Optional

- [Full text for AI tools](https://example.com/llms-full.txt)
```

It is the generator behind [irodev.net/llms.txt](https://irodev.net/llms.txt), made standalone.

## What these files are, honestly

- **llms.txt** is a proposal (Jeremy Howard, September 2024) for a Markdown file at your site root that gives
  language models a short summary of your site and links to its most useful pages.
- **llms-full.txt** is a common companion: the same index followed by the full content of those pages.
- **AGENTS.md** started as instructions for coding agents in repositories. On a website it is a plain page for AI
  agents that read or use the site on behalf of a person.

No major search engine documents reading llms.txt, and Google says AI features need no special files. Treat these
files as a courtesy for AI tools and agents that look for them, not as a ranking factor. Your pages, robots.txt and
structured data still do the real work.

## Installation

```bash
composer require irodevnet/llms-txt
```

## Usage

### llms.txt

`LlmsTxt` follows the proposal's shape: an H1 title, a `>` summary, optional detail paragraphs, then H2 sections that
hold lists of links with notes. The "Optional" section always comes last: a tool that needs a shorter context may skip it.

Build it from the same arrays or database rows as your pages, and pass HTML from your CMS through
`Markdown::plain()`. Link text and URLs are escaped for you.

### llms-full.txt

```php
use Irodev\LlmsTxt\LlmsFullTxt;

$full = new LlmsFullTxt($index);                       // the LlmsTxt above
foreach ($pages as $page) {
    $full->document($page['title'], $page['url'], $page['markdown'], $page['updated']);
}
echo $full;
```

Each document follows a horizontal rule, with its title as an H1 and its address (and date) on the next line.

### AGENTS.md

```php
use Irodev\LlmsTxt\AgentsMd;

echo AgentsMd::create('AGENTS.md: Example Studio')
    ->intro('Guidance for AI agents and assistants that read or use this website on behalf of a person.')
    ->section('Where to find things')
    ->item('Services: https://example.com/services')
    ->item('Contact: https://example.com/contact')
    ->section('Rules')
    ->item('Only send a message when the person you act for has asked you to.');
```

### Serving the files

```php
use Irodev\LlmsTxt\Response;

Response::text($index->render());      // llms.txt, llms-full.txt: text/plain; charset=utf-8
Response::markdown($agents->render()); // AGENTS.md: text/markdown; charset=utf-8
```

Both add `Cache-Control: public, max-age=3600` and `X-Robots-Tag: noindex`: the files are guides for machines, not
pages for search results, and robots.txt still lets crawlers fetch them. Use `Response::headers()` if your framework
sets headers itself. [`examples/serve.php`](examples/serve.php) is a complete script with the rewrite rules.

### Checking a file

```php
use Irodev\LlmsTxt\Validator;

$result = Validator::check($text);     // ['errors' => [...], 'warnings' => [...]]
```

From the command line:

```bash
php examples/check.php https://example.com/llms.txt
```

Errors break the format: the file doesn't start with an H1, has more than one H1, or is HTML. Warnings flag a
missing summary, list items that aren't links, relative or `http://` URLs, and empty sections. The validator is for
llms.txt; llms-full.txt has one H1 per document by design.

## Testing

```bash
composer install
composer test
```

## License

MIT © [İRODEV LTD](https://irodev.net). Built by İrodev, a web and software studio for businesses in Turkey and the
UK. Check how ready your site is for AI search with the [free AI visibility test](https://irodev.net/geo-ai-gorunurluk).
