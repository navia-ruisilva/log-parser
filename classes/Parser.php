<?php

abstract class Parser {
    
    protected $ip_patt = "[0-9]{1,3}(\.[0-9]{1,3}){3}";
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
        if ($optional || isset($pattern['optional'])) $p = "(" . $p . ")?";
        return $p;
    }

    public function build_pattern($fields) {

        if (empty($fields) || !is_array($fields)) {
            throw new Exception("Invalid fields array provided for pattern building.");
        }
        if (count($fields) == 0) {
            return $this->regex_delimiter . "^$" . $this->regex_delimiter; // Empty pattern
        }

        $patt = ""; $p = "^";
        foreach ($fields as $fk => $fv) {
                $patt .= $p . $this::build_patt_field($fk, $fv);
                $p = "\s+";
        }

        $patt = $this->regex_delimiter . $patt . $this->regex_delimiter;

        return $patt;
    }

    abstract public function parseLine($line);

}