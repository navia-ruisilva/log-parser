<?php

$opts= getopt('p:', ['parser:']);
if (empty($opts['p']) && empty($opts['parser'])) {
    die("Usage: php test.php -p <parser>\n");
}

$parserClass = ucfirst($opts['p'] ?? $opts['parser']);
$parserFile = "classes/parsers/{$parserClass}.php";
if (!file_exists($parserFile)) {
    die("Parser class file not found: {$parserFile}\n");
}
require_once $parserFile;
$parser = new $parserClass();
if (!($parser instanceof Parser)) {
    die("Invalid parser class: {$parserClass}\n");
}
while (!feof(STDIN)) {
    $line = trim(fgets(STDIN));
    if (empty($line)) continue;

    try {
        $data = $parser->parseLine($line);
        print_r($data);
    } catch (Exception $e) {
        echo "Error parsing line: {$line}\n";
        echo "Exception: {$e->getMessage()}\n";
    }
}