<?php

class MailLog extends Parser {

    protected $pattern = "~~";

    public function __construct() {
        parent::__construct();
    }

    public function parseLine($line) {
        if (preg_match($this->pattern, $line, $matches)) {
            return $matches;
        }
        throw new Exception("Failed to parse line: {$line}");
    }
}