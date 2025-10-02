<?php

require_once __DIR__ . '/AccessLog.php';

class AccessExLog extends AccessLog {
    
    protected $pattern = "";

    public function __construct() {
        //parent::__construct();
       
        $this->pattern = $this->_build_pattern();
        //$this->pattern = "~(?<clientip>{$this->ip_patt}) \((?<clientip2>{$this->ip_patt})\) \[(?<date>.*)\] \"(?<action>(-|(?<method>[a-zA-Z]+) (?<url>.*) HTTP/(?<httpversion>.*)))\" (?<status>[0-9]{3}) (?<connectionstatus>.) (?<bytesinput>[0-9]+) (?<bytesoutput>[0-9]+) (?<durationms>[0-9]+) \"(?<username>.*)\" \"(?<sessionid>.*)\"~";
    }

    private function _build_pattern() {
        // vhost.domain.com:443 1.2.3.4 - - [1/Jan/2000:10:11:12 +0000] "GET /home HTTP/1.1" 200 1234 "referer" "user_agent/v0"

        $patt_ip = $this->ip_patt;
        $patt_num = "[0-9]+";

        $fields['source_ip'] = $patt_ip;
        $fields['source_ip2'] = [
            'prefix' => "\(",
            'pattern' => $patt_ip,
            'suffix' => "\)"
        ];
        $fields['date'] = [
                'prefix' => "\[",
                'pattern' => ".*",
                'suffix' => "\]"
        ];
        $fields['action'] = [
                'prefix' => '"',
                'pattern' => '(-|' .
                        self::build_patt_field("method", "[A-Za-z-_]+") . "\s+" .
                        self::build_patt_field("path", "[^ ?]*") .
                        self::build_patt_field("query", ['prefix' => "\?", 'pattern' => ".*", 'optional' => true]) . "\s+" .
                        self::build_patt_field("httpversion", ['prefix' => "HTTP/", 'pattern' => "[0-9]+(\.[0-9]+)?"]) .
                        '|.*)',
                'suffix' => '"',
                'optional' => true
        ];
        $fields['status'] = "[0-9]{3}";
        $fields['connectionstatus'] = ".";
        $fields['bytesinput'] = $patt_num;
        $fields['bytesoutput'] = $patt_num;
        $fields['durationus'] = $patt_num;
        $fields['username'] = ['prefix' => '"', 'pattern' => ".*", 'suffix' => '"'];
        $fields['sessionid'] = ['prefix' => '"', 'pattern' => ".*", 'suffix' => '"'];

        return $this::build_pattern($fields);

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
        $matches['duration'] = $matches['durationus'] / 1000000;

        // Return the parsed data
        return $matches;
    }
}