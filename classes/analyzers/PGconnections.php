<?php
require_once __DIR__ . '/../Analyzer.php';
require_once __DIR__ . '/../Stats.php';

class PGconnections extends Analyzer {
    // This class would contain methods to analyze PostgreSQL connection logs
    // Implementation details would go here

    public function getSessionTimeInterval($session_time) {
        $patt = "/(?<hours>[0-9]{1,2}):(?<minutes>[0-9]{2}):(?<seconds>[0-9]{2})(\.(?<frac>[0-9]{1,3}))?/";
        if (preg_match($patt, $session_time, $matches)) {
            $hours = intval($matches['hours']);
            $minutes = intval($matches['minutes']);
            $seconds = intval($matches['seconds']);
            $frac = isset($matches['frac']) ? intval($matches['frac']) : 0;
            return ($hours * 3600) + ($minutes * 60) + $seconds + ($frac / 1000);
        }
        return null;
    }

    public function analyzeLine($linedata) {
        try {
            $this->_data[] = $linedata; // Store the parsed line data for later analysis
            // Additional analysis logic can be added here
        } catch (Exception $e) {
            throw new Exception("Error analyzing line: Exception: {$e->getMessage()}");
        }
    }

    public function analyze() {
        if (empty($this->_data)) {
            throw new Exception("No data to analyze. Please call analyzeLine() first.");
        }
        
        // Implement specific analysis logic here
        // For example, you could summarize, filter, or transform the data
        // This is just a placeholder for demonstration purposes
        $stats = new Stats();
        
        foreach ($this->_data as $entry) {
            if (isset($entry['connection_authorized'])) {
                $stats->incValue("authorized_connections");
            }
            if (isset($entry['disconnection'])) {
                $stats->incValue("disconnections");
                $session_time_str = $entry['disconnection_dc_session_time'] ?? null;
                if ($session_time_str !== null) {
                    $session_time_sec = $this->getSessionTimeInterval($session_time_str);
                    if ($session_time_sec !== null) {
                        $stats->addValue("session_time_seconds", $session_time_sec);
                    }
                }
            }
            if (isset($entry['connection_received'])) {
                $stats->incValue("received_connections");
            }
            
        }
        return $stats->getData();
    }
}