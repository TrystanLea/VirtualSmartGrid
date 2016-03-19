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
$connected = $redis->connect("127.0.0.1");

$logger = new EmonLogger();
require "PHPFina.php";
$phpfina = new PHPFina();
$phpfina->dir = "/home/user/smartgrid/data/";   // set to your user directory


while (true)
{

$smartgrid = array(
    "ukgrid"=>array(
        //"co2"=>0,     - data now brought in via the ukgrid tool api : https://github.com/TrystanLea/ukgrid
        //"wind"=>0,    - data now brought in via the ukgrid tool api : https://github.com/TrystanLea/ukgrid
        "solar"=>0 
    ),

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

//$smartgrid['ukgrid']['co2'] = 1*json_decode(file_get_contents("https://emoncms.org/feed/value.json?id=97715&apikey=8f5c2d146c0c338845d2201b8fe1b0e1"));
//$smartgrid['ukgrid']['wind'] = 1*json_decode(file_get_contents("https://emoncms.org/feed/value.json?id=67088&apikey=8f5c2d146c0c338845d2201b8fe1b0e1"));
$smartgrid['ukgrid']['solar'] = 1*json_decode(file_get_contents("https://emoncms.org/feed/value.json?id=114934&apikey=8f5c2d146c0c338845d2201b8fe1b0e1"));

$values = array();

//$values[] = $smartgrid['ukgrid']['co2'];
//$values[] = $smartgrid['ukgrid']['wind'];

foreach ($smartgrid['users'] as $user=>$u) {

    if (isset($u['consumption']) && $u['consumption']['id']!=0) {
        $id = $u['consumption']['id'];    
        $smartgrid['users'][$user]['consumption']['value'] = 1*json_decode(file_get_contents("https://emoncms.org/feed/value.json?id=$id&apikey=".$u['apikey']));
        $values[] = $smartgrid['users'][$user]['consumption']['value'];
        unset($smartgrid['users'][$user]['consumption']['id']);
        unset($smartgrid['users'][$user]['apikey']);
    }
    
    $smartgrid['users'][$user]['solar']['value'] = 0;
    if (isset($u['solar']) && $u['solar']['id']!=0) {
        $id = $u['solar']['id'];    
        $smartgrid['users'][$user]['solar']['value'] = 1*json_decode(file_get_contents("https://emoncms.org/feed/value.json?id=$id&apikey=".$u['apikey']));
        $values[] = $smartgrid['users'][$user]['solar']['value'];
        unset($smartgrid['users'][$user]['solar']['id']);
        unset($smartgrid['users'][$user]['apikey']);
    }
}

$time = time();

$redis->set("smartgrid",json_encode($smartgrid));

$phpfina->post(1,$time,$values);


echo ".";
sleep(10);

}

class EmonLogger {
    public function __construct() {}
    public function info ($message){ }
    public function warn ($message){ }
}
