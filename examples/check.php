<?php

declare(strict_types=1);

// Check any llms.txt against the proposal:
//   php examples/check.php https://example.com/llms.txt
//   php examples/check.php path/to/llms.txt

require __DIR__ . '/../vendor/autoload.php';

use Irodev\LlmsTxt\Validator;

$source = $argv[1] ?? '';
if ($source === '') {
    fwrite(STDERR, "Usage: php check.php <URL or file>\n");
    exit(2);
}
$text = @file_get_contents($source);
if ($text === false) {
    fwrite(STDERR, "Could not read $source\n");
    exit(2);
}
$result = Validator::check($text);
foreach ($result['errors'] as $e) {
    echo "ERROR    $e\n";
}
foreach ($result['warnings'] as $w) {
    echo "warning  $w\n";
}
echo $result['errors'] === [] ? "OK: valid llms.txt\n" : "Not valid\n";
exit($result['errors'] === [] ? 0 : 1);
