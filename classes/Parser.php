<?php

abstract class Parser {
    
    protected $ip_patt = "([0-9a-f:]+|[0-9]{1,3}(\.[0-9]{1,3}){3})";
    protected $pattern = "";
    protected $regex_delimiter = "`";

    public function __construct() {
        
    }

    public function getPattern() {
        return $this->pattern;
    }

    protected function build_patt_field($varname, $pattern, $optional = false) {
        $pfx = $pattern['prefix'] ?? '';
        $patt = $pattern['pattern'] ?? $pattern;
        $sfx = $pattern['suffix'] ?? '';

        $p = sprintf("%s(?<%s>%s)%s", $pfx, $varname, $patt, $sfx);
        if ($optional) $p = "(" . $p . ")?";
        return $p;
    }

    public function build_pattern($fields, $start = true) {
        $del = ($start ? $this->regex_delimiter : '');

        if (empty($fields) || !is_array($fields)) {
            throw new Exception("Invalid fields array provided for pattern building.");
        }
        if (count($fields) == 0) {
            return $del . "^$" . $del; // Empty pattern
        }

        $patt = ""; $p = $start ? "^" : '';
        foreach ($fields as $fk => $fv) {
            $op1 = (($fv['optional'] ?? false) ? "(" : "");
            $op2 = (($fv['optional'] ?? false) ? ")?" : "");
            $patt .= $op1 . $p . $this::build_patt_field($fk, $fv) . $op2;
            $p = "\s+";
        }

        $patt = $del . $patt . $del;

        return $patt;
    }

    abstract public function parseLine($line);

}