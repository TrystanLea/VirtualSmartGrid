<?php
$auth = "";
if (isset($_GET['auth'])) $auth = $_GET['auth'];
if ($auth!="authkey") die;
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Smart Grid Test</title>
  <meta name="description" content="">
  <meta name="author" content="">
  
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="//fonts.googleapis.com/css?family=Raleway:400,300,600" rel="stylesheet" type="text/css">
  <link rel="stylesheet" href="lib/skeleton/css/normalize.css">
  <link rel="stylesheet" href="lib/skeleton/css/skeleton.css">
  <link rel="icon" type="lib/skeleton/image/png" href="images/favicon.png">
  
  <script language="javascript" type="text/javascript" src="lib/jquery-1.11.3.min.js"></script>
  <script language="javascript" type="text/javascript" src="lib/flot/jquery.flot.min.js"></script>
  <script language="javascript" type="text/javascript" src="lib/flot/jquery.flot.selection.min.js"></script>
  <script language="javascript" type="text/javascript" src="lib/flot/jquery.flot.time.min.js"></script>
  <script language="javascript" type="text/javascript" src="lib/vis.helper3.js"></script>

  <style>
      button {
          padding: 0 18px;
      }
      
      body { 
          background-color:#333;
          color:#fff;
      }
      
      .container {
          max-width: 1200px;
      }
      
      button {
          color:#fff;
      }
      
      button:hover {
          color:#fff;
      }
      
      button:focus {
          color: #fff;
          border-color: #fff;
          outline: 0; 
      }
            
      .legend table {
          background:none;
      }
      
      .legend td {
          padding:0;
          padding-left:5px;
          border:0;
          color:#fff;
      }
  </style>
</head>
<body>

  <!-- Primary Page Layout
  –––––––––––––––––––––––––––––––––––––––––––––––––– -->
  <div class="container" style="text-align:center">
  
    <section class="header" style="margin-top: 5%">
        <h1>Smart Grid Test</h1>
    </section>
    
    <div class="row" style="text-align:center">
        
        <button class='time visnav' type='button' time='1'>DAY</button>
        <button class='time' type='button' time='7'>WEEK</button>
        <button class='time' type='button' time='30'>MONTH</button>
        <button class='time' type='button' time='365'>YEAR</button>
        <button id='zoomin' >ZOOM IN</button>
        <button id='zoomout' >ZOOM OUT</button>
        <button id='left' >LEFT</button>
        <button id='right' >RIGHT</button>
            
        <br>
        <div id="placeholder_bound">
              <div id="placeholder"></div>
        </div>
        
    </div>
    <div class="row" style="text-align:center; margin-top:20px">
        <div class="two columns">
            <h5>Generation</h4>
            <h4><span id="total_generation"></span> kWh</h4>
        </div>
        <div class="two columns">
            <h5>Consumption</h5>
            <h4><span id="total_consumption"></span> kWh</h4>
        </div>
        <div class="two columns">
            <h5>Backup</h5>
            <h4><span id="total_unmet"></span> kWh</h4>
        </div>
        <div class="two columns">
            <h5>Matching</h5>
            <h4><span id="matching"></span>%</h4>
        </div>
        <div class="two columns">
            <h5>gCO2/kWh</h5>
            <h4><span id="co2_intensity"></span></h4>
        </div>
        <div class="two columns">
            <h5>CO2</h5>
            <p>Emitted: <span id="total_co2"></span><span class="co2units"></span><br>
            <span style="color:#aaa">Displaced: <span id="displaced_co2"></span><span class="co2units"></span><br>
            Net: <span id="net_co2"></span><span class="co2units"></span></span></p>
        </div>
    </div>
    
        
    <div class="row" style="text-align:center"> 
        <br><br>
        <p>Crucial to getting right the transition to the zero-carbon energy system is taking a step back and looking at the wider view beyond the boundary of the home: how efficient building fabric, electrified heating and transport link together and match supply from renewable energy both on-site and from further afield.</p>
        <p>Aggregation of renewable supply and demand across a number of sites can be one important method of increasing the degree of supply/demand matching in the system. Not everyone boils a kettle at the same time or a cloud passing over one households solar pv system may not be passing over another.</p>
        
        <p><a href="https://openenergymonitor.org">OpenEnergyMonitor</a></p>
    </div>
  </div>

<!-- End Document
  –––––––––––––––––––––––––––––––––––––––––––––––––– -->
</body>
</html>


<script>
var auth = "<?php echo $auth; ?>";
var path = "https://openenergymonitor.org/dev/smartgrid/";

var timeWindow = (3500*24.0*1);
var interval = 60;

view.end = (+new Date)*0.001;
view.end = Math.floor(view.end / interval) * interval;
view.start = view.end - timeWindow;
view.start = Math.floor(view.start / interval) * interval;

$('#placeholder').width($('#placeholder_bound').width());
$('#placeholder_bound').height($('#placeholder_bound').width()*0.5);
$('#placeholder').height($('#placeholder_bound').width()*0.5);

var options = {
    series: { lines: { show: true, fill:true } },
    xaxis: { min: view.start*1000, max: view.end*1000, mode: "time", timezone: "browser" },
    selection: { mode: "x" },
    legend: {show:false}
};
var series = [];
    
    
var solar = [];
var consumption = [];
var grid = {};

reload();

function reload() {

    var npoints = 1000;
    interval = (view.end - view.start) / npoints;
    interval = view.round_interval(interval);
    view.end = Math.floor(view.end / interval) * interval;
    view.start = Math.floor(view.start / interval) * interval;

    solar = getdata("aggregation",view.start,view.end,interval,0,0);
    consumption = getdata("consumption",view.start,view.end,interval,0,0);
    
    grid = load_grid_data(view.start,view.end,interval);
    
    load();
}

// ------------------------------------------------------------------------
// LOAD: 
// This is called when we need to draw or redraw the existing loaded dataset
// ------------------------------------------------------------------------
function load()
{
    var totalsolar = [];
    var totalwind = [];
    var totalhydro = [];
    var totalconsumption = [];
    var total_co2_data = [];
    var total_co2_inc_displaced_data = [];
    var gridco2_data = [];
    
    var ukgridco2 = 350;
    var virtualgridco2 = [];
    
    
    var wind_now = 0;
    var hydro_now = 0;
    var solar_now = 0;
    var consumption_now = 0;

    var totaluse = 0;
    var totalgen = 0;
    var totalunmet = 0;
    var totalexess = 0;
    
    var total_co2 = 0;
    var total_co2_displaced = 0;
    
    var total_co2_inc_displaced = 0;
    var gridco2intensity = 0;
    
    var intensity = {};
    intensity["CCGT"] = 360;
    intensity["COAL"] = 910;
    intensity["NUCLEAR"] = 0;

    var n = 0;
    for (var t=view.start; t<view.end; t+=interval) {
        
        // WIND
        if (grid['WIND'].data[n][1]!=null) iwind = grid['WIND'].data[n][1];
        if (grid['NPSHYD'].data[n][1]!=null) ihydro = grid['NPSHYD'].data[n][1];
        
        // ----------------------------------------------------------------
        // Grid co2 intensity based on selected generation
        // ----------------------------------------------------------------
        var gridco2intensity_tmp = 0;
        var griddemand = 0;
        for (var z in intensity) {
            var value = 1.0 * grid[z].data[n][1];
            gridco2intensity_tmp += value * intensity[z];
            griddemand += value;
        }
        
        if (griddemand>0 && griddemand<70000) {
            gridco2intensity_tmp = gridco2intensity_tmp / griddemand; // normalise
            gridco2intensity_tmp = gridco2intensity_tmp / 0.93;       // grid losses 7%
            gridco2intensity = gridco2intensity_tmp;
        }
                
        // WIND
        var annual_kwh_of_grid_wind = 2000 * 3;
        var average_wind_power = 2650; // MW
        var average_power = ((annual_kwh_of_grid_wind/365.0)/0.024); // 375W
        var wind_now = Math.round((average_power / average_wind_power) * iwind);
        
        // HYDRO
        var annual_kwh_of_grid_hydro = 600 * 3;
        var average_hydro_power = 687; // MW
        var average_power = ((annual_kwh_of_grid_hydro/365.0)/0.024); // 375W
        var hydro_now = Math.round((average_power / average_hydro_power) * ihydro);

        // Onsite: 
        
        // SOLAR
        var solar_now = solar[n][1];
        if (solar_now<0) solar_now = 0;
        
        // CONSUMPTION
        var consumption_now = consumption[n][1];
        if (consumption_now<0) consumption_now = 0;
                
        // WIND, SOLAR, CONSUMPTION
        totalhydro.push([t*1000,hydro_now]); 
        totalwind.push([t*1000,hydro_now+wind_now]);
        totalsolar.push([t*1000,hydro_now+wind_now+solar_now]);
        totalconsumption.push([t*1000,consumption_now]);
        gridco2_data.push([t*1000,gridco2intensity]);
        
        var supply = hydro_now + wind_now + solar_now 
        var demand = consumption_now;
        
        var balance = supply - demand;
        
        var imported = 0;
        var exess = 0;
        
        if (balance<0) {
            imported = balance * -1;
        } else {
            exess = balance;
        }
        
        var co2 = gridco2intensity * (imported/consumption_now);
        
        total_co2 += (interval * imported / 3600000.0) * gridco2intensity
        total_co2_data.push([t*1000,total_co2]);
        
        total_co2_displaced += (interval * exess / 3600000.0) * gridco2intensity
        
        total_co2_inc_displaced += (interval * (-1*balance) / 3600000.0) * gridco2intensity;
        total_co2_inc_displaced_data.push([t*1000,total_co2_inc_displaced]);
        
        virtualgridco2.push([t*1000,co2]);
        
        totaluse += (demand * interval) / 3600000.0;
        totalgen += (supply * interval) / 3600000.0;
        totalunmet += (imported * interval) / 3600000.0;
        totalexess += (exess * interval) / 3600000.0;
        n++;
    }
    
    var matching = (1.0 - (totalunmet / totaluse))*100;
    
    
    $("#matching").html((matching).toFixed(0));
    $("#total_generation").html((totalgen).toFixed(0));
    $("#total_consumption").html((totaluse).toFixed(0));
    $("#total_unmet").html((totalunmet).toFixed(0));
    $("#co2_intensity").html((total_co2/totaluse).toFixed(0));
    
    
    var co2scale = 1;
    if (total_co2>1000) {
        co2scale = 0.001;
        dp = 1;
        $(".co2units").html("kg");
    } else {
        dp = 0;
        $(".co2units").html("g");
    }
    if (total_co2>10000) dp=0;
    
    $("#total_co2").html((total_co2*co2scale).toFixed(dp));
    $("#displaced_co2").html((total_co2_displaced*co2scale).toFixed(dp));
    $("#net_co2").html(((total_co2 - total_co2_displaced)*co2scale).toFixed(dp));
    
    
    var series = [];
    series.push({label:"Solar", data:totalsolar, yaxis:1, color: "#dccc1f", lines:{show:true, fill:1.0, lineWidth: 0}});
    series.push({label:"Wind", data:totalwind, yaxis:1, color: "#2ed52e", lines:{show:true, fill:1.0, lineWidth: 0}});
    series.push({label:"Hydro", data:totalhydro, yaxis:1, color: "#3333cc", lines:{show:true, fill:1.0, lineWidth: 0}});
    series.push({label:"Consumption", data:totalconsumption, yaxis:1, color: "#0699fa", lines:{show:true, fill:0.6, lineWidth: 0}});
    
    series.push({label:"Virtual Grid CO2 Intensity", data:virtualgridco2, yaxis:2, color: "#ff5500", lines:{show:true, fill:false, lineWidth: 1}});
    series.push({label:"Grid CO2 Intensity", data:gridco2_data, yaxis:2, color: "#ff0000", lines:{show:true, fill:false, lineWidth: 1}});
    series.push({label:"Emitted CO2", data:total_co2_data, yaxis:3, color: "#aaa", lines:{show:true, fill:false, lineWidth: 1}});
    series.push({label:"Net CO2", data:total_co2_inc_displaced_data, yaxis:3, color: "#888", lines:{show:true, fill:false, lineWidth: 1}});

    var options = {
      xaxis: {
          min: view.start*1000, max: view.end*1000, mode: "time", timezone: "browser"
      },
      selection: { mode: "x", color:"#000" },
      legend: {show:true, position: "nw", backgroundColor:"none", color:"#fff"} 
    };
  
    $.plot("#placeholder",series, options);
}

$("#zoomout").click(function () {view.zoomout(); reload();});
$("#zoomin").click(function () {view.zoomin(); reload();});
$('#right').click(function () {view.panright(); reload();});
$('#left').click(function () {view.panleft(); reload();});
$('.time').click(function () {view.timewindowseconds($(this).attr("time")); reload();});

$('#placeholder').bind("plotselected", function (event, ranges) {
    view.start = ranges.xaxis.from * 0.001;
    view.end = ranges.xaxis.to * 0.001;
    reload();
});

$(window).resize(function(){
    $('#placeholder').width($('#placeholder_bound').width());
    $('#placeholder_bound').height($('#placeholder_bound').width()*0.6);
    $('#placeholder').height($('#placeholder_bound').width()*0.6);
    load();
});

function getdata(feedid,start,end,interval,skipmissing,limitinterval)
{
    var data = [];
    $.ajax({                                      
        url: path+"data",                      
        data: "id="+feedid+"&start="+(start*1000)+"&end="+(end*1000)+"&interval="+interval+"&skipmissing="+skipmissing+"&limitinterval="+limitinterval+"&auth="+auth,
        dataType: 'json',
        async: false,                      
        success: function(data_in) { data = data_in;
        } 
    });
    return data;
}

function load_grid_lastvalue() {
    var gridlastvalue = {};
    
    $.ajax({                                      
        url: "https://openenergymonitor.org/ukgrid/api.php?q=lastvalue&id=1",
        async: false,
        dataType: "json",
        success: function(result) {
            gridlastvalue = result;
        }
    });
    
    var values = {};
    
    var grid = {
      "WIND":{label:"Wind", color:"rgba(0,255,0,0.6)",column:5,yaxis:1,stack: true},
      "PS":{label:"Pumped storage",color:"rgba(0,150,255,0.6)",column:6,yaxis:1,stack: true},
      "NPSHYD":{label:"Hydro",color:"rgba(0,50,255,0.6)",column:7,yaxis:1,stack: true},
      "OTHER":{label:"Other",color:"rgba(227,162,36,0.6)",column:8,yaxis:1,stack: true},
      "INTFR":{label:"French Interconnector",color:"rgba(227,197,140,0.6)",column:9,yaxis:1,stack: true},
      "INTIRL":{label:"Irish Interconnector",color:"rgba(227,197,140,0.6)",column:10,yaxis:1,stack: true},
      "INTNED":{label:"Dutch Interconnector",color:"rgba(227,197,140,0.6)",column:11,yaxis:1,stack: true},
      "INTEW":{label:"East-West Interconnector",color:"rgba(227,197,140,0.6)",column:12,yaxis:1,stack: true},
      "NUCLEAR":{label:"Nuclear",color:"rgba(227,225,36,0.6)",column:4,yaxis:1,stack: true},
      "OIL":{label:"Oil",color:"rgba(50,50,50,0.6)",column:2,yaxis:1,stack: true},
      "COAL":{label:"Coal",color:"rgba(0,0,0,0.6)",column:3,yaxis:1,stack: true},
      "CCGT":{label:"Closed cycle gas turbine",color:"rgba(0,100,255,0.6)",column:0,yaxis:1,stack: true},
      "OCGT":{label:"Open cycle gas turbine",color:"rgba(0,100,200,0.6)",column:1,yaxis:1,stack: true},
      "DEMAND":{label:"Demand",color:"rgba(255,0,0,0.6)",column:14,yaxis:1,stack: false,fill:false,show:false}
      //"INTENSITY":{label:"CO2 Intensity",color:"rgba(255,255,255,0.6)",column:13,yaxis:2,stack: false,fill:false,show:false},
      //"INTENSITY_SMOOTH":{label:"CO2 Intensity smooth",color:"rgba(255,255,255,1.0)",yaxis:2,stack: false,fill:false,show:true}
    };
    
    for (var z in grid) {
        values[z] = gridlastvalue.values[grid[z].column];
    }
    
    return values;
}

function load_grid_data(start,end,interval) {

  // The grid data is loaded in maximum 5 minute resolution
  // We map the 5 minute grid data onto the same interval base as the main data request
  // start, end and interval for which provided as parameters
  
  var grid_data_interval = Math.round((end - start) / 288);
  if (grid_data_interval<300) grid_data_interval = 300;
  
  var grid = {
    "WIND":{label:"Wind", color:"rgba(0,255,0,0.6)",column:5,yaxis:1,stack: true},
    "PS":{label:"Pumped storage",color:"rgba(0,150,255,0.6)",column:6,yaxis:1,stack: true},
    "NPSHYD":{label:"Hydro",color:"rgba(0,50,255,0.6)",column:7,yaxis:1,stack: true},
    "OTHER":{label:"Other",color:"rgba(227,162,36,0.6)",column:8,yaxis:1,stack: true},
    "INTFR":{label:"French Interconnector",color:"rgba(227,197,140,0.6)",column:9,yaxis:1,stack: true},
    "INTIRL":{label:"Irish Interconnector",color:"rgba(227,197,140,0.6)",column:10,yaxis:1,stack: true},
    "INTNED":{label:"Dutch Interconnector",color:"rgba(227,197,140,0.6)",column:11,yaxis:1,stack: true},
    "INTEW":{label:"East-West Interconnector",color:"rgba(227,197,140,0.6)",column:12,yaxis:1,stack: true},
    "NUCLEAR":{label:"Nuclear",color:"rgba(227,225,36,0.6)",column:4,yaxis:1,stack: true},
    "OIL":{label:"Oil",color:"rgba(50,50,50,0.6)",column:2,yaxis:1,stack: true},
    "COAL":{label:"Coal",color:"rgba(0,0,0,0.6)",column:3,yaxis:1,stack: true},
    "CCGT":{label:"Closed cycle gas turbine",color:"rgba(0,100,255,0.6)",column:0,yaxis:1,stack: true},
    "OCGT":{label:"Open cycle gas turbine",color:"rgba(0,100,200,0.6)",column:1,yaxis:1,stack: true},
    "DEMAND":{label:"Demand",color:"rgba(255,0,0,0.6)",column:14,yaxis:1,stack: false,fill:false,show:false}
    //"INTENSITY":{label:"CO2 Intensity",color:"rgba(255,255,255,0.6)",column:13,yaxis:2,stack: false,fill:false,show:false},
    //"INTENSITY_SMOOTH":{label:"CO2 Intensity smooth",color:"rgba(255,255,255,1.0)",yaxis:2,stack: false,fill:false,show:true}
  };
  
  $.ajax({
    url: "https://openenergymonitor.org/ukgrid/api.php?q=data&id=1&start="+(start*1000)+"&end="+(end*1000)+"&interval="+grid_data_interval,
    async: false,
    dataType: "json",
    success: function(griddata) {
    
      for (var z in grid) {
        grid[z].data = [];
      }
      
      var n = 0;
      var values = {};
      for (var t=start; t<end; t+=interval) {
          
          var pos = Math.floor((t-start) / grid_data_interval);
          for (var z in grid) {
              if (values[z]==undefined) values[z] = 0;
              if (griddata[pos][grid[z].column]!=null) {
                  values[z] = griddata[pos][grid[z].column];
              }
              grid[z].data.push([t*1000,values[z]]); 
          }
          n++;
      }
    }
  });
  return grid;
}
</script>

