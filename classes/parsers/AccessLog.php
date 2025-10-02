<?php

require_once __DIR__ . '/../Parser.php';

class AccessLog extends Parser {
    
    public function __construct() {
        parent::__construct();
    
        $this->pattern = $this->_build_pattern();
    }

    private function _build_pattern() {
        // vhost.domain.com:443 1.2.3.4 - - [1/Jan/2000:10:11:12 +0000] "GET /home HTTP/1.1" 200 1234 "referer" "user_agent/v0"

        $patt_ip = $this->ip_patt;
        $patt_num = "[0-9]+";

        $fields['vhost'] = "[a-zA-Z0-9-]+(\.[a-zA-Z0-9-]+)*(:[0-9]+)?";
        $fields['source_ip'] = $patt_ip;
        $fields['remote_logname'] = "(-|[a-zA-Z0-9-.]+)";
        $fields['remote_user'] = "(-|[a-zA-Z0-9-.]+)";
        $fields['date'] = [
                'prefix' => "\[",
                'pattern' => ".*",
                'suffix' => "\]"
        ];
        $fields['action'] = [
                'prefix' => '"',
                'pattern' =>
                        self::build_patt_field("method", "[A-Za-z-]+") . "\s+" .
                        self::build_patt_field("path", "[^ ?]*") .
                        self::build_patt_field("query", ['prefix' => "\?", 'pattern' => ".*", 'optional' => true]) . "\s+" .
                        self::build_patt_field("httpversion", ['prefix' => "HTTP/", 'pattern' => "[0-9]+(\.[0-9]+)?"]),
                'suffix' => '"',
                'optional' => true
        ];
        $fields['status'] = "[0-9]{3}";
        $fields['outputbytes'] = $patt_num;
        $fields['referer'] = ['prefix' => '"', 'pattern' => ".*", 'suffix' => '"'];
        $fields['useragent'] = ['prefix' => '"', 'pattern' => ".*", 'suffix' => '"'];

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

        // Return the parsed data
        return $matches;
    }
}