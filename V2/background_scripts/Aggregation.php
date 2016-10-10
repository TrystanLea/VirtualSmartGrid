<?php

class Aggregation
{
    public $dir = "";
    
    public function __construct() {}
    
    public function exists($name) 
    {
        if (!file_exists($this->dir.$name.".meta")) return false;
        if (!file_exists($this->dir.$name.".dat")) return false;
        return true;
    }
    
    public function create($name,$start,$end,$interval,$fillvalue)
    {    
        $start = floor($start / $interval) * $interval;
        $end = floor($end / $interval) * $interval;
        $npoints = ($end - $start) / $interval;
        
        // Build blank aggregation
        $buffer = "";
        for ($i=0; $i<$npoints; $i++) {
            $buffer .= pack("f",$fillvalue);
        }
        
        // Write the aggregation buffer to disk
        $fh = fopen($this->dir.$name.".dat","wb");
        fwrite($fh,$buffer);
        fclose($fh);
        
        // Create meta file
        $metafile = fopen($this->dir.$name.".meta", 'wb');
        fwrite($metafile,pack("IIII",0,0,$interval,$start));
        fclose($metafile);
    }
    
    public function getmeta($name) 
    {
        $meta = new stdClass();
        $metafile = fopen($this->dir.$name.".meta", 'rb');
        
        fseek($metafile,8);

        $tmp = unpack("I",fread($metafile,4)); 
        $meta->interval = $tmp[1];
        
        $tmp = unpack("I",fread($metafile,4)); 
        $meta->start_time = $tmp[1];
        
        fclose($metafile);
        
        clearstatcache($this->dir.$name.".dat");
        $filesize = filesize($this->dir.$name.".dat");
        $meta->npoints = floor($filesize / 4.0);
        
        return $meta;
    }
    
    public function sum($name, $data_start, $data_interval, $databinary) 
    {
        $data_npoints = strlen($databinary) / 4;
        
        $meta = $this->getmeta($name);

        $fh = fopen($this->dir.$name.".dat","rb");

        $buffer = "";
        for ($i=0; $i<$meta->npoints; $i++) {
            // Read datapoint from aggregation layer
            $time = $meta->start_time + ($meta->interval * $i);
            $tmp = unpack("f",fread($fh,4));
            $value = $tmp[1];
            
            // Read and sum values of datapoint from feed to be added
            $data_pos = floor(($time - $data_start) / $data_interval);
            if ($data_pos>=0 && $data_pos<$data_npoints) {
                $tmp = unpack("f",substr($databinary,$data_pos*4,4));
                if (!is_nan($tmp[1])) $value += $tmp[1];
            }
            
            // print $time." ".$value."\n";
            $buffer .= pack("f",$value);
        }
        fclose($fh);

        $fh = fopen($this->dir.$name.".dat","wb");
        fwrite($fh,$buffer);
        fclose($fh);
    }
}
