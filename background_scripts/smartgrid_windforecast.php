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

  $url = "http://www.bmreports.com/bsp/additional/saveoutput.php?element=windfcoutturn&output=XML";

  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL,$url);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  $output = curl_exec ($ch);
  curl_close ($ch);

  $forecast = simplexml_load_string($output);
  $redis->set("cache:windforecast",json_encode($forecast));

