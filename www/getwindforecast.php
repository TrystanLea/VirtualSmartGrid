<?php

  $redis = new Redis();
  $connected = $redis->connect("localhost",6379);

  if (!$redis->exists("cache:windforecast")) {
      $url = "http://www.bmreports.com/bsp/additional/saveoutput.php?element=windfcoutturn&output=XML";

      $ch = curl_init();
      curl_setopt($ch, CURLOPT_URL,$url);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
      $output = curl_exec ($ch);
      curl_close ($ch);

      $forecast = simplexml_load_string($output);
      $redis->set("cache:windforecast",json_encode($forecast));
  }

  $forecast = json_decode($redis->get("cache:windforecast"));
  
  // SP: Settlment period
  // FC_ORIG_VALUE
  // ORIG_FC_TIME
  // FC_VALUE
  // FC_TIME
  // OUTTURN_VAL
  // OUTTURN_TIME
  // $forecast->DAY_DATA[0]->SP[0]->{'@attributes'}->SP;
  
  $data = array();
  
  foreach ($forecast->DAY_DATA as $k=>$v)
  {
      $time_SD = strtotime($forecast->DAY_DATA[$k]->{'@attributes'}->SD);
      
      foreach ($forecast->DAY_DATA[$k]->SP as $sp) 
      {
          $time_SP = $time_SD + $sp->{'@attributes'}->SP * 1800;
          
          $fc_orig_value = $sp->{'@attributes'}->FC_ORIG_VALUE*1;
          if ($fc_orig_value=="NULL") $fc_orig_value = null;
          $fc_value = $sp->{'@attributes'}->FC_VALUE*1;
          if ($fc_value=="NULL") $fc_value = null;
          $outturn_value = $sp->{'@attributes'}->OUTTURN_VAL*1;
          if ($outturn_value=="NULL") $outturn_value = null;
                    
          $data[] = array($time_SP, $fc_orig_value, $fc_value, $outturn_value);
          // print $time_SP." ".$sp->{'@attributes'}->FC_ORIG_VALUE." ".$sp->{'@attributes'}->FC_VALUE." ".$sp->{'@attributes'}->OUTTURN_VAL."\n";
          
      }
  
  }
  
  print json_encode($data);
