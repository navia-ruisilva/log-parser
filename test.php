<?php

function usage() {
    echo "Usage: php test.php -p <parser> -a|--analyzer <analyzer>\n";
    echo "Example: php test.php -p AccessExLog -a AccessExAnalyzer\n";
    exit(1);
}

$opts= getopt('p:a:', ['parser:', 'analyzer:']);
if (empty($opts['p']) && empty($opts['parser'])) {
    usage();
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

echo $parser->getPattern() . "\n";


if (!empty($opts['a']) || !empty($opts['analyzer'])) {
    $analyzerClass = ucfirst($opts['a'] ?? $opts['analyzer']);
    $analyzerFile = "classes/analyzers/{$analyzerClass}.php";
    if (!file_exists($analyzerFile)) {
        die("Analyzer class file not found: {$analyzerFile}\n");
    }
    require_once $analyzerFile;
    
    $analyzer = new $analyzerClass($parser);
}


$n = 0;
$data = [];
while (!feof(STDIN)) {
    $line = trim(fgets(STDIN));
    if (empty($line)) continue;
    $n++;
    try {
        
        $linedata = $parser->parseLine($line);
        if (isset($analyzer)) {
            $analyzer->analyzeLine($linedata);
        } else {
            $data[] = $linedata;
            print_r($linedata);
        }
    } catch (Exception $e) {
        echo "Error parsing line: {$line}\n";
        echo "Exception: {$e->getMessage()}\n";
    }
}
printf("Parsed %d lines.\n", $n);

if (isset($analyzer)) {
    $result = $analyzer->analyze();
    
    foreach ($result as $key => $value) {
        if (is_array($value)) {
            echo "{$key}:\n";
            foreach ($value as $subKey => $subValue) {
                echo "  {$subKey}: {$subValue}\n";
            }
        } else {
            echo "{$key}: {$value}\n";
        }
    }
} else {
    print_r($data);
}