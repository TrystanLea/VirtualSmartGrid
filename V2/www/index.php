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

error_reporting(E_ALL);
ini_set('display_errors', 'on');

$q = "";
if (isset($_GET['q'])) $q = $_GET['q'];

$auth = false;
if (isset($_GET['auth']) && $_GET['auth']=="AUTHKEY") $auth = true;
if (isset($_GET['apikey']) && $_GET['apikey']=="AUTHKEY") $auth = true;

$logger = new EmonLogger();

require "PHPFina.php";
$phpfina = new PHPFina();
$phpfina->dir = "/home/user/scripts1/store/";

switch ($q)
{   
    case "solar":
        header('Content-Type: text/html');
        print file_get_contents("solar.html");
        break;

    case "":
        header('Content-Type: text/html');
        print file_get_contents("front.html");
        break;
        
    case "history":
        header('Content-Type: text/html');
        if ($auth) print view("history.php",array());
        break;
        
    case "data":
        header('Content-Type: application/json');
        
        $name = get('id');
        
        if ($name=="aggregation") {
            print json_encode($phpfina->get_data($name,get('start'),get('end'),get('interval'),get('skipmissing'),get('limitinterval')));
        }

        if ($name=="consumption" && $auth) {
            print json_encode($phpfina->get_data($name,get('start'),get('end'),get('interval'),get('skipmissing'),get('limitinterval')));
        }
        
        break;
        
    case "feed/data.json":
        header('Content-Type: application/json');
        
        $name = get('id');
        
        if ($name=="aggregation") {
            print json_encode($phpfina->get_data($name,get('start'),get('end'),get('interval'),get('skipmissing'),get('limitinterval')));
        }

        if ($name=="consumption" && $auth) {
            print json_encode($phpfina->get_data($name,get('start'),get('end'),get('interval'),get('skipmissing'),get('limitinterval')));
        }
        
        break;
    
    case "lastvalue":
        header('Content-Type: application/json');
        
        $name = get('id');
        
        if ($name=="aggregation") {
            print json_encode($phpfina->lastvalue(get('id')));
        }
        
        if ($name=="consumption" && $auth) {
            print json_encode($phpfina->lastvalue(get('id')));
        }
        
        if ($name=="uksolar") {
            print $redis->get("uksolar");
        }
        break;
        
    case "virtualgridstatus": 
        print virtualgridstatus();
        break;
}
    
function get($index) {
    $val = null;
    if (isset($_GET[$index])) $val = $_GET[$index];
    if (get_magic_quotes_gpc()) $val = stripslashes($val);
    return $val;
}

function view($filepath, array $args)
{
    extract($args);
    ob_start();
    include "$filepath";
    $content = ob_get_clean();
    return $content;
}

class EmonLogger {
    public function __construct() {}
    public function info ($message){ }
    public function warn ($message){ }
}

// ------------------------------------------------------------------------------------
// Advanced anonymisation functions
// ------------------------------------------------------------------------------------

function virtualgridstatus() {
    global $redis;
    
    // if you know supply, grid co2 and community co2 can you work out consumption?
    // balance = supply - consumption
    
    $total_consumption = $redis->get("virtualgrid:consumption");
    $solar = $redis->get("virtualgrid:solar");
    $total_balance = $solar - $total_consumption;
    $marginalgridco2intensity = $redis->get("gridintensity");
    
    $joules = 0;
    if ($total_balance<0) $joules = $total_balance * -1 * 10.0;
    $gco2 = $marginalgridco2intensity * ($joules/3600000.0);
    $CO2 = $gco2 / (($total_consumption*10)/3600000.0);

    $status = "amber";
    if ($CO2<10.0) $status = "green";
    if ($CO2>150.0) $status = "red";
    
    $status = "green";
    return $status;
}
