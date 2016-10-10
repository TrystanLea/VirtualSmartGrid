<?php

  /*

  Source code is released under the GNU Affero General Public License.
  See COPYRIGHT.txt and LICENSE.txt.

  ---------------------------------------------------------------------
  OpenEnergyMonitor VirtualSmartGrid - Open source virtual smart grid renewable energy aggregation and sharing concept with a focus on carbon metrics.

  Part of the OpenEnergyMonitor project:
  http://openenergymonitor.org

  */

  $interval = 30;
  $period = 3600*24;
  $end = floor(time() / $interval)*$interval;
  $start = $end - $period;
  $npoints = $period / $interval;
  
  $redis = new Redis();
  $connected = $redis->connect("localhost",6379);

  $smartgrid = array(
    "users"=>array(
        "H1"=>array(
            "apikey"=>"APIKEY USER 1",
            "consumption"=>array("id"=>0),
            "solar"=>array("id"=>0)
        ),
        "H2"=>array(
            "apikey"=>"APIKEY USER 2",
            "consumption"=>array("id"=>0),
            "solar"=>array("id"=>0)
        )
    )
  );
  
  $tmp_store = array();
  $columns = 0;
  
  $apikey = "8f5c2d146c0c338845d2201b8fe1b0e1";
  $feedid = 97715; // GRID CO2
  $data = json_decode(file_get_contents("http://emoncms.org/feed/data.json?id=$feedid&start=".($start*1000)."&end=".($end*1000)."&interval=$interval&skipmissing=0&limitinterval=0&apikey=$apikey"));
  print count($data)."\n";
  $tmp_store[] = $data;
  $columns++;
  
  $feedid = 67088; // UK WIND
  $data = json_decode(file_get_contents("http://emoncms.org/feed/data.json?id=$feedid&start=".($start*1000)."&end=".($end*1000)."&interval=$interval&skipmissing=0&limitinterval=0&apikey=$apikey"));
  print count($data)."\n";
  $tmp_store[] = $data;
  $columns++;
  
  foreach ($smartgrid['users'] as $user) 
  {
      $apikey = $user['apikey'];
      
      if ($user['consumption']['id']>0) {
          $feedid = $user['consumption']['id'];
          $data = json_decode(file_get_contents("http://emoncms.org/feed/data.json?id=$feedid&start=".($start*1000)."&end=".($end*1000)."&interval=$interval&skipmissing=0&limitinterval=0&apikey=$apikey"));
          print count($data)."\n";
          $tmp_store[] = $data;
          $columns++;
      }
      
      if ($user['solar']['id']>0) {
          $feedid = $user['solar']['id'];
          $data = json_decode(file_get_contents("http://emoncms.org/feed/data.json?id=$feedid&start=".($start*1000)."&end=".($end*1000)."&interval=$interval&skipmissing=0&limitinterval=0&apikey=$apikey")); 
          print count($data)."\n";
          $tmp_store[] = $data;
          $columns++;
      }
  }
  
  $store = array();
  for ($n=0; $n<$npoints; $n++) {
      for ($col=0; $col<$columns; $col++) {
          $store[$n][$col] = round($tmp_store[$col][$n][1]);
      }
  }
  
  foreach ($store as $line) {
      print implode(",",$line)."\n";
  }
  
  $logger = new EmonLogger();
  require "PHPFina.php";
  $phpfina = new PHPFina();
  $phpfina->dir = "/home/user/smartgrid/data/";
  
  print "Creating feed 1, interval:$interval, columns:$columns\n";
  if ($phpfina->create(1,$interval,$columns)==true) {
      print "feed created\n";
      if ($phpfina->postblock(1,$start,$interval,$store)) {
          print "data post success\n";
      }
  }
  
  /*
  $json = $phpfina->get_data(1,$start*1000,$end*1000,$interval,0,0);
  foreach ($json as $line) {
  print implode(",",$line)."\n";
  }*/
  
  
  class EmonLogger {
      public function __construct() {}
      public function info ($message){ }
      public function warn ($message){ }
  }
