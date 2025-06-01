<?php

require_once __DIR__ . '/AccessLog.php';

class AccessExLog extends AccessLog {
    
    protected $pattern = "";

    public function __construct() {
        parent::__construct();
       

        $this->pattern = "~(?<clientip>{$this->ip_patt}) \((?<clientip2>{$this->ip_patt})\) \[(?<date>.*)\] \"(?<action>(-|(?<method>[a-zA-Z]+) (?<url>.*) HTTP/(?<httpversion>.*)))\" (?<status>[0-9]{3}) (?<connectionstatus>.) (?<bytesinput>[0-9]+) (?<bytesoutput>[0-9]+) (?<durationms>[0-9]+) \"(?<username>.*)\" \"(?<sessionid>.*)\"~";
    }


    
}