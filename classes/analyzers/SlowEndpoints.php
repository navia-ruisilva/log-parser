<?php

require_once __DIR__ . '/../Analyzer.php';
require_once __DIR__ . '/../Stats.php';

class SlowEndpoints extends Analyzer {
    
    protected $parser;
    private $stats;

    public function __construct($parser) {
        if (!($parser instanceof Parser)) {
            throw new Exception("Invalid parser provided.");
        }
        $this->parser = $parser;
        $this->stats = new Stats();
    }

    public function analyzeLine($linedata) {
        try {
            if (isset($linedata['duration']) && $linedata['duration'] < 0) {
                return; // Skip lines with response time less than 1000ms
            }
            $this->_data[] = $linedata; // Store the parsed line data for later analysis
            $endpoint = $linedata['action'] ?? 'unknown';
            $this->stats->addValue($endpoint, $linedata['duration']);
            //print_r($linedata);
        } catch (Exception $e) {
            throw new Exception("Error analyzing line: {$linedata}. Exception: {$e->getMessage()}");
        }
    }

    public function analyze() {
        if (empty($this->_data)) {
            throw new Exception("No data to analyze. Please call analyzeLine() first.");
        }
        
        // Implement specific analysis logic here
        // For example, you could summarize, filter, or transform the data
        // This is just a placeholder for demonstration purposes
        
        


        foreach ($this->stats->getKeys() as $endpoint) {
            $this->stats->getStats($endpoint);            
        }

        $top = $this->stats->getTop(5, 'max');
        
        return $top;
    }
}