<?php

abstract class Parser {
    
    protected $ip_patt = "[0-9]{1,3}(\.[0-9]{1,3}){3}";
    protected $pattern = "";

    public function __construct() {
        
    }

    abstract public function parseLine($line);

}