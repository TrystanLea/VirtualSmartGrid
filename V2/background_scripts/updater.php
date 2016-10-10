<?php

$logger = new EmonLogger();

require "PHPFina.php";
$phpfina = new PHPFina();
$phpfina->dir = "store/";

$redis = new Redis();
$connected = $redis->connect("127.0.0.1");

require "gridintensity.php";

$solarfeeds = array (
  array("id"=>0, "apikey"=>"READ_APIKEY"),   // generation feeds
);

$consumptionfeeds = array (
  array("id"=>1, "apikey"=>"READ_APIKEY")    // consumption feeds
);



while(true) 
{
    $sum = 0.0;

    foreach ($solarfeeds as $solarfeed) {
        $timevalue = json_decode(file_get_contents("http://emoncms.org/feed/timevalue.json?id=".$solarfeed['id']."&apikey=".$solarfeed['apikey']));
        
        $time = time();
        $lastupdated = ($time - $timevalue->time);
        
        print "Feed:".$solarfeed['id'].", Value:".$timevalue->value."W, ".$lastupdated."s ago\n";
        
        if ($lastupdated<900) $sum += (float) $timevalue->value;
    }

    print "Solar: ".$sum."\n";
    $phpfina->post("aggregation",time(),$sum);
    $redis->set("virtualgrid:solar",$sum);


    $sum = 0;

    foreach ($consumptionfeeds as $consumptionfeed) {
        $timevalue = json_decode(file_get_contents("http://emoncms.org/feed/timevalue.json?id=".$consumptionfeed['id']."&apikey=".$consumptionfeed['apikey']));
        
        $time = time();
        $lastupdated = ($time - $timevalue->time);
        
        print "Feed:".$consumptionfeed['id'].", Value:".$timevalue->value."W, ".$lastupdated."s ago\n";
        
        if ($lastupdated<900) $sum += $timevalue->value;
    }

    print "Consumption: ".$sum."\n";
    $phpfina->post("consumption",time(),$sum); 
    $redis->set("virtualgrid:consumption",$sum);   
    
    
    // UK Solar data from emoncms.org
    $uksolar = 1*json_decode(file_get_contents("https://emoncms.org/feed/value.json?id=114934&apikey=8f5c2d146c0c338845d2201b8fe1b0e1"));
    $redis->set("uksolar",$uksolar);
    print "UK Solar: ".$uksolar."\n";
    
    $gridintensity = gridintensity();
    $redis->set("gridintensity",$gridintensity);
    print "Grid intensity: ".$gridintensity."\n";
    
    sleep(10);
}

class EmonLogger {
    public function __construct() {}
    public function info ($message){ }
    public function warn ($message){ }
}
