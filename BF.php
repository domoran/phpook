<?php
namespace Continental\RMDX\Utils;

class BF {
        protected $data = array(0);
        protected $dataIndex = 0;
        protected $source = 0;
        protected $sourceIndex = 0;
        protected $step = 0;
        protected $output = array(); 
        protected $input = null; 
        protected $inputIndex = 0; 
        protected $maxStep = 0; 
        protected $maxData = 0; 
        
        protected $loopStarts = array(); 
        protected $loopEnds   = array(); 
        
        protected $log = array(); 
    
        public function __construct ($source, $maxData = 100000, $maxStep = 100000) {
            $this->source = $source;
            $this->sourceLen = strlen($source); 
            $this->maxData = $maxData; 
            $this->maxStep = $maxStep;

            
        }
        
        protected function parseLoops () {
            $loops = array(); 
            for ($i = 0; $i < $this->sourceLen; $i++) {
                $c = $this->source[$i];
                 
                if ($c == '[') {
                    array_push($loops, $i); 
                }
                if ($c == ']') {
                    if (count($loops) == 0) $this->raiseException("Unmatched loop end at index $i encountered!"); 
                    $start = array_pop($loops); 
                    $this->loopStarts[$start] = $i; 
                    $this->loopEnds[$i] = $start;
                }
            }
            
            if (count($loops) !== 0) $this->raiseException("Unmatched loop start at index " . array_pop($loops) . " encountered!");
        }
        
        protected function logStep() {
            if ($this->inputIndex < strlen($this->input)) {
                $iChar = $this->input[$this->inputIndex];
                if (ctype_print($iChar)) $iChar = ord($iChar) . "(" . $iChar . ")"; else $iChar = ord($iChar);   
            } else {
                $iChar = "(null)";
            }
            if (count($this->data) <= $this->dataIndex) $iData = 0; else $iData = $this->data[$this->dataIndex];
            $this->log[] = array($this->step, $this->sourceIndex, $this->source[$this->sourceIndex], $this->dataIndex, $iData, $this->inputIndex, $iChar);
        }
        
        public function formatLog($strm) {
            fwrite($strm, "Step\tSource Ptr\tCommand\tData Ptr\tData\tInput Index\tNext Input\n");
            foreach ($this->log as $logentry) {
                if (!is_array($logentry)) {
                    fwrite($strm, "$logentry\n");
                } else {
                    $step  = $logentry[0];
                    $si    = $logentry[1];
                    $sdata = $logentry[2];
                    $di    = $logentry[3];
                    $data  = $logentry[4];
                    $ii    = $logentry[5];
                    $idata = $logentry[6];
                    fwrite($strm, "$step\t$si\t$sdata\t$di\t$data\t$ii\t$idata\n");
                }
            }
        }
        
        protected function raiseException($msg) {
            $this->log[] = $msg;
            throw new \Exception($msg); 
        }
    
        protected function step () {
            if ($this->sourceIndex >= $this->sourceLen) return false;
            
            $this->logStep();
            
            $this->step++; 
            if ($this->step > $this->maxStep) {
                
                $this->raiseException("Maximum allowed execution steps ($this->maxStep) reached!");
            }
            
            $char = $this->source[$this->sourceIndex];
            
            switch ($char) {
                case '.': { 
                    $this->output[] = chr($this->data[$this->dataIndex]);
                    $this->sourceIndex++;
                    break;
                }
                case '>': {
                    $this->dataIndex++; 
                    if ($this->dataIndex > $this->maxData) $this->raiseException("Maximum data size ($this->maxData) exeeded!");
                    if (count($this->data) == $this->dataIndex) $this->data[] = 0;
                    $this->sourceIndex++;
                    break;
                }
                case '<': {
                    if ($this->dataIndex > 0) $this->dataIndex--;
                    $this->sourceIndex++;
                    break;
                }                
                case '+': {
                    $this->data[$this->dataIndex] = (($this->data[$this->dataIndex]+1) & 255); 
                    $this->sourceIndex++;
                    break;
                }
                case '-': {
                    $this->data[$this->dataIndex] = (($this->data[$this->dataIndex]-1) & 255);
                    $this->sourceIndex++;
                    break;
                }
                case ',': {
                    if ($this->inputIndex < strlen($this->input)) {
                        $this->data[$this->dataIndex] = ord($this->input[$this->inputIndex]);
                        $this->inputIndex++;
                    }
                    $this->sourceIndex++;
                    break;
                }
                case '[':
                    $data = $this->data[$this->dataIndex]; 
                    if ($data == 0) {
                        $this->sourceIndex = $this->loopStarts[$this->sourceIndex] + 1;
                    } else {
                        $this->sourceIndex++;
                    }
                    break;
                case ']': 
                    $data = $this->data[$this->dataIndex];
                    if ($data == 0) {
                        $this->sourceIndex++;
                    } else {
                        $this->sourceIndex = $this->loopEnds[$this->sourceIndex]+1;
                    }
                    break;
                default: {
                    $this->sourceIndex++;
                }
            }
            
            return true; 
        }
        
        public function execute ($input) {
            $this->input = $input; 
            $this->parseLoops();
            while ($this->step()); 
            return $this->getOutput();  
        }
        
        public function getOutput() {
            return implode("", $this->output);
        }
        
        public function getData() {
            return $this->data;
        }
}