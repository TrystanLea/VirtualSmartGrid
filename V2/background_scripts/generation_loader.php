<?php

require "Aggregation.php";
$feed = new Aggregation();
$feed->dir = "store/";

$name = "aggregation";

$aggregation_interval = 10;
$aggregation_end = time();
$aggregation_end = floor($aggregation_end/$aggregation_interval) * $aggregation_interval;

$aggregation_start = $aggregation_end - (3600*24*90);

//if (!$feed->exists($name)) {
$feed->create($name,$aggregation_start,$aggregation_end,$aggregation_interval,0);
//}

$solarfeeds = array (
  array("id"=>1, "apikey"=>"WRITE_APIKEY")
);

foreach ($solarfeeds as $solarfeed) {
    print "loading feed ".$solarfeed["id"]."\n";
    $data = load_data($solarfeed["id"],$aggregation_start,$solarfeed["apikey"]);
    $feed->sum($name, $data['start_time'], $data['interval'], $data['binary']);
}

// ----------------------------------------------------------------------------------------------------
// Feed loader
// ----------------------------------------------------------------------------------------------------

function load_data($feedid,$start_time,$apikey)
{
    // The data loader uses the export api on emoncms.org in order to provide a fast non time and 
    // resolution restricted way of loading historic data into an aggregation.
    
    // The export api returns the binary data from the target feed starting from a file position.
    // In order to set the position to match the aggregation period we first need to download the 
    // meta data which gives a start time and interval from which the start position can be calculated.
    
    $server = "https://emoncms.org/";
    $meta = json_decode(file_get_contents($server."feed/getmeta.json?id=$feedid&apikey=$apikey"));
    if ($start_time<$meta->start_time) $start_time = $meta->start_time;
    $pos = floor(($start_time - $meta->start_time) / $meta->interval);
    $downloadfrom = $pos * 4;
    $start_time = $meta->start_time + ($pos * $meta->interval);
    
    // Download from the position in the target feed to start downloading from:

    $url = $server."feed/export.json?apikey=$apikey&id=$feedid&start=$downloadfrom";
    if (!$primary = @fopen( $url, 'r' )) {
        echo "Cannot access remote server\n";
        return false;
    }

    $databinary = "";
    if ($primary)
    {
        // Discard first as it is an update at time start_time - interval
        fread($primary,4); 
        
        for (;;) {
            $databinary .= fread($primary,8192);
            if (feof($primary)) break;
        }
    }

    fclose($primary);
    
    return array(
        "start_time"=>$start_time,
        "interval"=>$meta->interval,
        "binary"=>$databinary
    );
}
