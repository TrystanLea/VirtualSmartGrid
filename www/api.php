<?php
/*

Source code is released under the GNU Affero General Public License.
See COPYRIGHT.txt and LICENSE.txt.

---------------------------------------------------------------------
OpenEnergyMonitor VirtualSmartGrid - Open source virtual smart grid renewable energy aggregation and sharing concept with a focus on carbon metrics.

Part of the OpenEnergyMonitor project:
http://openenergymonitor.org

*/
    
if (!isset($_GET['auth'])) die;
if ($_GET['auth']!="AUTHENTICATION_KEY") die;

$redis = new Redis();
$connected = $redis->connect("127.0.0.1");

error_reporting(E_ALL);
ini_set('display_errors', 'on');

if (!isset($_GET['q'])) die;

$q = $_GET['q'];

$logger = new EmonLogger();

require "PHPFina.php";
$phpfina = new PHPFina();
$phpfina->dir = "/home/user/smartgrid/data/";

header('Content-Type: application/json');
switch ($q)
{   
    case "smartgrid":
        print $redis->get("smartgrid");
        break;

    // case "create":
    //    print $phpfina->create($id,array("interval"=>get('interval'), "columns"=>get('columns')));
    //    break;
    
    // case "post":
    //    if (isset($_GET['apikey']) && $apikey == $_GET['apikey']) { 
    //        $time = time();
    //        print json_encode($phpfina->post($id,$time,explode(",",get('values'))));
    //    }
    //    break;

    case "data":
        
        print json_encode($phpfina->get_data(get('id'),get('start'),get('end'),get('interval'),get('skipmissing'),get('limitinterval')));
        break;
    
    case "lastvalue":
        print json_encode($phpfina->lastvalue(get('id')));
        break;
}
    
function get($index)
{
    $val = null;
    if (isset($_GET[$index])) $val = $_GET[$index];
    
    if (get_magic_quotes_gpc()) $val = stripslashes($val);
    return $val;
}

class EmonLogger {
    public function __construct() {}
    public function info ($message){ }
    public function warn ($message){ }
}
