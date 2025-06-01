<?php

require_once __DIR__ . '/../Parser.php';

class AccessLog extends Parser {
    
    public function __construct() {
        parent::__construct();
    
        $this->pattern = "~(?<clientip>{$this->ip_patt}) \((?<clientip2>{$this->ip_patt})\) \[(?<date>.*)\] \"(?<action>(-|(?<method>[a-zA-Z]+) (?<url>.*) HTTP/(?<httpversion>.*)))\" (?<status>[0-9]{3}) (?<connectionstatus>.) (?<bytesinput>[0-9]+) (?<bytesoutput>[0-9]+) (?<durationms>[0-9]+) \"(?<username>.*)\" \"(?<sessionid>.*)\"~";
    }

    public function parseLine($line) {
        if (empty($line)) {
            return [];
        }

        $matches = [];
        if (!preg_match($this->pattern, $line, $matches)) {
            throw new Exception("Line does not match the expected format: {$line}");
        }

        // Convert duration from milliseconds to seconds
        $matches['duration'] = $matches['durationms'] / 1000;

        // Return the parsed data
        return $matches;
    }
}