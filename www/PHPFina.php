<?php

/*

Source code is released under the GNU Affero General Public License.
See COPYRIGHT.txt and LICENSE.txt.

---------------------------------------------------------------------
From Emoncms

Part of the OpenEnergyMonitor project:
http://openenergymonitor.org

*/

// This timeseries engine implements:
// Fixed Interval No Averaging

class PHPFina
{
    public $dir = "/var/lib/phpfina/";
    private $log;
    public $padding_mode = "nan";
    
    /**
     * Constructor.
     *
     * @api
    */

    public function __construct()
    {
        $this->log = new EmonLogger(__FILE__);
    }

    /**
     * Create feed
     *
     * @param integer $id The id of the feed to be created
    */
    public function create($id,$interval,$columns)
    {
        $interval = (int) $interval;
        if ($interval<5) $interval = 5;
        
        $columns = (int) $columns;
        if ($columns<1) $columns = 1;
        
        // Check to ensure we dont overwrite an existing feed
        if (!$meta = $this->get_meta($id))
        {
            // Set initial feed meta data
            $meta = new stdClass();
            $meta->interval = $interval;
            $meta->columns = $columns;
            
            $meta->start_time = 0;
            $meta->npoints = 0;
            
            // Save meta data
            $this->create_meta($id,$meta);
            
            $fh = @fopen($this->dir.$id.".dat", 'c+');
            
            if (!$fh) {
                $this->log->warn("PHPFina:create could not create data file id=$id");
                return false;
            }
            fclose($fh);
        }
        
        $feedname = "$id.meta";
        if (file_exists($this->dir.$feedname)) {
            return true;
        } else {
            $this->log->warn("PHPFina:create failed to create feed id=$id");
            return false;
        }
    }

    /**
     * Adds a data point to the feed
     *
     * @param integer $id The id of the feed to add to
     * @param integer $time The unix timestamp of the data point, in seconds
     * @param float $value The value of the data point
    */
    public function post($id,$timestamp,$values)
    {
        $this->log->info("PHPFina:post post id=$id timestamp=$timestamp value=".json_encode($values));
        
        $id = (int) $id;
        $timestamp = (int) $timestamp;
        
        $now = time();
        $start = $now-(3600*24*365*5); // 5 years in past
        $end = $now+(3600*48);         // 48 hours in future
        
        if ($timestamp<$start || $timestamp>$end) {
            $this->log->warn("PHPFina:post timestamp out of range");
            return false;
        }
        
        // If meta data file does not exist then exit
        if (!$meta = $this->get_meta($id)) {
            $this->log->warn("PHPFina:post failed to fetch meta id=$id");
            return false;
        }
        
        // Calculate interval that this datapoint belongs too
        $timestamp = floor($timestamp / $meta->interval) * $meta->interval;
        
        // If this is a new feed (npoints == 0) then set the start time to the current datapoint
        if ($meta->npoints == 0 && $meta->start_time==0) {
            $meta->start_time = $timestamp;
            $this->create_meta($id,$meta);
        }

        if ($timestamp < $meta->start_time) {
            $this->log->warn("PHPFina:post timestamp older than feed start time id=$id");
            return false; // in the past
        }	

        // Calculate position in base data file of datapoint
        $pos = floor(($timestamp - $meta->start_time) / $meta->interval);

        $last_pos = $meta->npoints - 1;

        // if ($pos<=$last_pos) {
        // return false;
        // }

        $fh = fopen($this->dir.$id.".dat", 'c+');
        if (!$fh) {
            $this->log->warn("PHPFina:post could not open data file id=$id");
            return false;
        }
        
        // Write padding
        $padding = ($pos - $last_pos)-1;
        
        // Max padding = 1 million datapoints ~4mb gap of 115 days at 10s
        $maxpadding = 1000000;
        
        if ($padding>$maxpadding) {
            $this->log->warn("PHPFina:post padding max block size exeeded id=$id, $padding dp");
            return false;
        }
        
        // Sanitise input data
        for ($i=0; $i<$meta->columns; $i++) {
            $values[$i] = (float) $values[$i];
        }
        
        if ($padding>0) {
            
            $buffer = "";
            for ($i=0; $i<$padding; $i++) {
                for ($c=0; $c<$meta->columns; $c++) {
                    $buffer .= pack("f",NAN);
                }
            }
            fseek($fh,4*$meta->npoints*$meta->columns);
            fwrite($fh,$buffer);
            
        } else {
            //$this->log->warn("PHPFINA padding less than 0 id=$id");
            //return false;
        }
        
        // Write new datapoint
	      fseek($fh,4*$pos*$meta->columns);
	      
	      for ($c=0; $c<$meta->columns; $c++) {
            if (!is_nan($values[$c])) {
                fwrite($fh,pack("f",$values[$c])); 
            } else {
                fwrite($fh,pack("f",NAN));
            }
        }
        // Close file
        fclose($fh);
        
        return $values;
    }

    public function postblock($id,$start_time,$interval,$block)
    {
        // If meta data file does not exist then exit
        if (!$meta = $this->get_meta($id)) {
            return false;
        }
        
        // Block post interval needs to be the same as the feed interval
        if ($meta->interval != $interval) {
            return false;
        }
        
        // Number of columns in the block needs to be the same as the meta
        if ($meta->columns != count($block[0])) {
            return false;
        }
        
        // If this is a new feed (npoints == 0) then set the start time to the current datapoint
        if ($meta->npoints == 0 && $meta->start_time==0) {
            $meta->start_time = $start_time;
            $this->create_meta($id,$meta);
        }
        
        // start_time needs to be not less than the feed start_time
        if ($start_time < $meta->start_time) {
            return false;
        }
        
        $pos = floor(($start_time - $meta->start_time) / $meta->interval);
        $last_pos = $meta->npoints - 1;
        $padding = ($pos - $last_pos)-1;
        
        // data needs to append directly or overlap
        // ability to padd is not implemented here yet.
        if ($padding>0) return false;
        
        $fh = fopen($this->dir.$id.".dat", 'c+');
        if (!$fh) {
            return false;
        }
        
        // Write new datapoint
	      fseek($fh,4*$pos*$meta->columns);
	      
	      for ($n=0; $n<count($block); $n++)
	      {
	          for ($c=0; $c<$meta->columns; $c++) {
                if (!is_nan($block[$n][$c])) {
                    fwrite($fh,pack("f",$block[$n][$c])); 
                } else {
                    fwrite($fh,pack("f",NAN));
                }
            }
        }
        
        // Close file
        fclose($fh);
    
    }

    /**
     * Return the data for the given timerange
     *
     * @param integer $id The id of the feed to fetch from
     * @param integer $start The unix timestamp in ms of the start of the data range
     * @param integer $end The unix timestamp in ms of the end of the data range
     * @param integer $dp The number of data points to return (used by some engines)
    */

    public function get_data($name,$start,$end,$interval,$skipmissing,$limitinterval)
    {
        $skipmissing = (int) $skipmissing;
        $limitinterval = (int) $limitinterval;
        $start = intval($start/1000);
        $end = intval($end/1000);
        $interval= (int) $interval;
        
        // Minimum interval
        if ($interval<1) $interval = 1;
        // End must be larger than start
        if ($end<=$start) return array('success'=>false, 'message'=>"request end time before start time");
        // Maximum request size
        $req_dp = round(($end-$start) / $interval);
        if ($req_dp>3000) return array('success'=>false, 'message'=>"request datapoint limit reached (3000), increase request interval or time range, requested datapoints = $req_dp");
        
        // If meta data file does not exist then exit
        if (!$meta = $this->get_meta($name)) return array('success'=>false, 'message'=>"error reading meta data $meta");
        // $meta->npoints = $this->get_npoints($name);
        
        if ($limitinterval && $interval<$meta->interval) $interval = $meta->interval; 

        $data = array();
        $time = 0; $i = 0;
        $numdp = 0;
        // The datapoints are selected within a loop that runs until we reach a
        // datapoint that is beyond the end of our query range
        $fh = fopen($this->dir.$name.".dat", 'rb');
        while($time<=$end)
        {
            $time = $start + ($interval * $i);
            $pos = round(($time - $meta->start_time) / $meta->interval);

            $line = array();
            for ($c=0; $c<$meta->columns; $c++) {
                $line[$c] = null;
            }

            if ($pos>=0 && $pos < $meta->npoints)
            {
                // read from the file
                fseek($fh,$pos*4*$meta->columns);
                $vals = unpack("f*",fread($fh,4*$meta->columns));

                for ($c=0; $c<$meta->columns; $c++) {
                    $val = $vals[$c+1];
                    if (is_nan($val)) $val = null;
                    $line[$c] = $val;
                }
            }
            
            // if ($value!==null || $skipmissing===0) {
            // $data[] = array($time*1000,$line);
            $data[] = $line;
            // }

            $i++;
        }
        return $data;
    }
    
    /**
     * Get the last value from a feed
     *
     * @param integer $id The id of the feed
    */
    public function lastvalue($id)
    {
        $id = (int) $id;
        
        // If meta data file does not exist then exit
        if (!$meta = $this->get_meta($id)) return false;
        
        if ($meta->npoints>0)
        {
            $fh = fopen($this->dir.$id.".dat", 'rb');
            $size = $meta->npoints*4*$meta->columns;
            
            fseek($fh,$size-4*$meta->columns);
            $d = fread($fh,4*$meta->columns);
            fclose($fh);

            $vals = unpack("f*",$d);
            $time = date("Y-n-j H:i:s", $meta->start_time + $meta->interval * $meta->npoints);
            
            $line = array();
            foreach ($vals as $val) {
                if (is_nan($val)) $val = null;
                $line[] = $val;
            }
            
            return array('time'=>$time, 'values'=>$line);
        }
        else
        {
            return array('time'=>0, 'value'=>0);
        }
    }
    
    public function delete($id)
    {
        if (!$meta = $this->get_meta($id)) return false;
        unlink($this->dir.$id.".meta");
        unlink($this->dir.$id.".dat");
    }
    
    public function get_feed_size($id)
    {
        if (!$meta = $this->get_meta($id)) return false;
        return (filesize($this->dir.$id.".meta") + filesize($this->dir.$id.".dat"));
    }
    

    public function get_meta($id)
    {
        $id = (int) $id;
        $feedname = "$id.meta";
        
        if (!file_exists($this->dir.$feedname)) {
            $this->log->warn("PHPFina:get_meta meta file does not exist id=$id");
            return false;
        }
        
        $meta = new stdClass();
        $metafile = fopen($this->dir.$feedname, 'rb');

        fseek($metafile,8);
        
        $tmp = unpack("I",fread($metafile,4)); 
        $meta->interval = $tmp[1];
        
        $tmp = unpack("I",fread($metafile,4)); 
        $meta->start_time = $tmp[1];

        $tmp = unpack("I",fread($metafile,4)); 
        $meta->columns = $tmp[1];
        
        fclose($metafile);
        
        clearstatcache($this->dir.$id.".dat");
        $filesize = filesize($this->dir.$id.".dat");
        $meta->npoints = floor($filesize / (4.0*$meta->columns));
        
        if ($meta->start_time>0 && $meta->npoints==0) {
            $this->log->warn("PHPFina:get_meta start_time already defined but npoints is 0");
            return false;
        }
  
        return $meta;
    }
    
    private function create_meta($id,$meta)
    {
        $id = (int) $id;
        
        $feedname = "$id.meta";
        $metafile = fopen($this->dir.$feedname, 'wb');
        
        if (!$metafile) {
            $this->log->warn("PHPFina:create_meta could not open meta data file id=".$id);
            return false;
        }
        
        if (!flock($metafile, LOCK_EX)) {
            $this->log->warn("PHPFina:create_meta meta file id=".$id." is locked by another process");
            fclose($metafile);
            return false;
        }
        
        fwrite($metafile,pack("I",0));
        fwrite($metafile,pack("I",0)); 
        fwrite($metafile,pack("I",$meta->interval));
        fwrite($metafile,pack("I",$meta->start_time));
        fwrite($metafile,pack("I",$meta->columns));
        fclose($metafile);
    }
}
