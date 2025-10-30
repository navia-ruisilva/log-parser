<?php

require_once __DIR__ . '/classes/parsers/PostgreSQLLog.php';

$parser = new PostgreSQLLog();

echo "Using pattern: " . base64_encode($parser->getPattern()) . "\n";
var_dump($parser->getPattern());