<?php

class Stats {
    
    private $data = [];
 
    public function incValue($key, $increment = 1) {
        if (!isset($this->data[$key])) {
            $this->data[$key] = 0;
        }
        $this->data[$key] += $increment;
    }
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
        return $this->data[$key]['average'] = $this->data[$key]['acc'] / $this->data[$key]['n'];
    }
    public function calculateVariance($key) {
        if (!isset($this->data[$key]) || $this->data[$key]['n'] == 0) {
            return null;
        }
        if (!isset($this->data[$key]['average'])) $this->calculateAverage($key);
        $mean = $this->data[$key]['average'];
        return $this->data[$key]['variance'] = ($this->data[$key]['acc2'] / $this->data[$key]['n']) - ($mean * $mean);
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
    
    public function getTop($n = 10, $sortBy = 'max') {
        if (!in_array($sortBy, ['min', 'max', 'average', 'n'])) {
            throw new InvalidArgumentException("Invalid sortBy parameter: {$sortBy}");
        }
        
        $sorted = $this->data;
        uasort($sorted, function($a, $b) use ($sortBy) {
            return $b[$sortBy] <=> $a[$sortBy];
        });
        
        return array_slice($sorted, 0, $n);
    }

    public function __toString() {
        return json_encode($this->data, JSON_PRETTY_PRINT);
    }
}