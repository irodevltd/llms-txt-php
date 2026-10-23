# Changelog

## 1.0.0 – 2026-10-23

First release, extracted from the generator behind [irodev.net/llms.txt](https://irodev.net/llms.txt).

- `LlmsTxt`: title, summary, details and linked sections in the shape of the llms.txt proposal; "Optional" last.
- `LlmsFullTxt`: the index followed by full documents.
- `AgentsMd`: guidance for AI agents that use a website.
- `Validator`: errors and warnings against the proposal; `examples/check.php` checks any URL or file.
- `Response`: text/plain or text/markdown with caching and noindex headers.
- `Markdown`: one-line text, escaped link text and URLs, plain text from HTML.
