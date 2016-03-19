<?php

  /*

  Source code is released under the GNU Affero General Public License.
  See COPYRIGHT.txt and LICENSE.txt.

  ---------------------------------------------------------------------
  OpenEnergyMonitor VirtualSmartGrid - Open source virtual smart grid renewable energy aggregation and sharing concept with a focus on carbon metrics.

  Part of the OpenEnergyMonitor project:
  http://openenergymonitor.org

  */

  $redis = new Redis();
  $connected = $redis->connect("localhost",6379);

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
