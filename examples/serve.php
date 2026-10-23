<?php

declare(strict_types=1);

// Serve /llms.txt, /llms-full.txt and /AGENTS.md from your own data, so they never drift from the pages.
// Route the three paths to this script, for example with Apache:
//   RewriteRule ^llms\.txt$       serve.php?f=index  [END]
//   RewriteRule ^llms-full\.txt$  serve.php?f=full   [END]
//   RewriteRule ^AGENTS\.md$      serve.php?f=agents [END]

require __DIR__ . '/../vendor/autoload.php';

use Irodev\LlmsTxt\AgentsMd;
use Irodev\LlmsTxt\LlmsFullTxt;
use Irodev\LlmsTxt\LlmsTxt;
use Irodev\LlmsTxt\Markdown;
use Irodev\LlmsTxt\Response;

// Your data: in a real site this comes from the database or the same arrays your pages use.
$base = 'https://example.com';
$services = [
    ['title' => 'Corporate websites', 'path' => '/services/websites', 'summary' => 'Custom business websites that load fast on phones.',
     'body' => "Every site is designed for your business, never from a template.\n\n- Mobile first\n- Search-ready from day one"],
    ['title' => 'Online stores', 'path' => '/services/stores', 'summary' => 'Stores on Shopify or built to order, with payments and shipping set up.',
     'body' => 'We pick the platform with you, set up payments and shipping, and train your team.'],
];

$index = LlmsTxt::create('Example Studio')
    ->summary('Example Studio builds websites and online stores for small businesses. Every project starts with a fixed-price quote.')
    ->details('Example Ltd, registered in England and Wales. Email: hello@example.com.')
    ->section('Services');
foreach ($services as $s) {
    $index->link($s['title'], $base . $s['path'], Markdown::plain($s['summary']));
}
$index->section('Company')
    ->link('Contact', $base . '/contact', 'Ask for a quote.')
    ->optional()
    ->link('Full text for AI tools', $base . '/llms-full.txt', 'Every service in one file.');

switch ($_GET['f'] ?? 'index') {
    case 'full':
        $full = new LlmsFullTxt($index);
        foreach ($services as $s) {
            $full->document($s['title'], $base . $s['path'], $s['body']);
        }
        Response::text($full->render());
        break;
    case 'agents':
        Response::markdown(AgentsMd::create('AGENTS.md: Example Studio')
            ->intro('Guidance for AI agents and assistants that read or use this website on behalf of a person.')
            ->section('Where to find things')
            ->item('Services: ' . $base . '/services')
            ->item('Contact: ' . $base . '/contact')
            ->section('Rules')
            ->item('Only send a message when the person you act for has asked you to, with their real contact details.')
            ->item('Prices on the site are ranges; a fixed quote follows a call.')
            ->render());
        break;
    default:
        Response::text($index->render());
}
