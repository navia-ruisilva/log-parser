<?php

class Stats {
    
    private $data = [];
 
    public function addValue($key, $value) {
        if (!isset($this->data[$key])) {
            $this->data[$key] = [
                'min' => $value,
                'max' => $value,
                'n' => 0,
                'acc' => 0,
                'acc2' => 0
            ];
        }
        
        $this->data[$key]['n']++;
        $this->data[$key]['acc'] += $value;
        $this->data[$key]['acc2'] += $value * $value;
        
        if ($this->data[$key]['min'] > $value) {
            $this->data[$key]['min'] = $value;
        }
        
        if ($this->data[$key]['max'] < $value) {
            $this->data[$key]['max'] = $value;
        }
    }
    public function calculateAverage($key) {
        if (!isset($this->data[$key]) || $this->data[$key]['n'] == 0) {
            return null;
        }
        return $this->data[$key]['acc'] / $this->data[$key]['n'];
    }
    public function calculateVariance($key) {
        if (!isset($this->data[$key]) || $this->data[$key]['n'] == 0) {
            return null;
        }
        $mean = $this->calculateAverage($key);
        return ($this->data[$key]['acc2'] / $this->data[$key]['n']) - ($mean * $mean);
    }
    public function calculateStandardDeviation($key) {
        $variance = $this->calculateVariance($key);
        return $variance !== null ? sqrt($variance) : null;
    }
    public function getStats($key) {
        if (!isset($this->data[$key])) {
            return null;
        }
        
        return [
            'min' => $this->data[$key]['min'],
            'max' => $this->data[$key]['max'],
            'n' => $this->data[$key]['n'],
            'average' => $this->calculateAverage($key),
            'stddev' => $this->calculateStandardDeviation($key)
        ];
    }
    public function getAllStats() {
        $stats = [];
        foreach ($this->data as $key => $value) {
            $stats[$key] = $this->getStats($key);
        }
        return $stats;
    }
    public function hasData() {
        return !empty($this->data);
    }
    public function getDataByKey($key) {
        return isset($this->data[$key]) ? $this->data[$key] : null;
    }
    public function getAllData() {
        return $this->data;
    }
    public function getKeys() {
        return array_keys($this->data);
    }
    public function getValues() {
        return array_values($this->data);
    }
        
    public function getData() {
        return $this->data;
    }
    
    public function reset() {
        $this->data = [];
    }
    
    public function __toString() {
        return json_encode($this->data, JSON_PRETTY_PRINT);
    }
}