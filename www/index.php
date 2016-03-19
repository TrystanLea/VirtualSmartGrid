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

?>

<style>
body {
    font-family:arial;
    background-color:#eee;    
}

.table {
    width:100%;
}

.box {
    padding: 20px;
    margin-left: 20px;
    margin-top:20px;
    background-color:#fff;  
}

.box-dark {
    padding: 20px;
    margin-left: 20px;
    margin-top:20px;
    background-color:#333;  
    color:#fff;
}



</style>

<meta http-equiv="content-type" content="text/html; charset=UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<script type="text/javascript" src="jquery-1.9.1.min.js"></script>

<!--[if lte IE 8]><script language="javascript" type="text/javascript" src="flot/excanvas.min.js"></script><![view.endif]-->
<script language="javascript" type="text/javascript" src="flot/jquery.js"></script>
<script language="javascript" type="text/javascript" src="flot/jquery.flot.js"></script>
<script language="javascript" type="text/javascript" src="flot/jquery.flot.stack.js"></script>
<script language="javascript" type="text/javascript" src="flot/jquery.flot.time.min.js"></script>
<script language="javascript" type="text/javascript" src="flot/jquery.flot.selection.min.js"></script>

<script language="javascript" type="text/javascript" src="vis.helper.js"></script>




<div class="box" style="width:600px;  height:900px;  float:left;">

    <h3>Smart Grid Test</h3>
    
    <p><b>Grid: <span id="co2"></span>gCO2/kWh (Marginal: <span id="marginalco2"></span> gCO2/kWh)<br>Wind: <span id="ukwind"></span>MW, Solar: <span id="uksolar"></span>MW, Hydro: <span id="ukhydro"></span>MW</b></p>
    
    <div id="status" style="padding:10px">
    Status: Red, high CO2, low wind
    </div>
    <br>
    <div id="group-status" style="padding:10px">
    Status: Red, high CO2, low wind
    </div>
    
    <p>Testing aggregation...</p>
    <p style="font-size:80%"><i>Green tariffs: Wind share is based on wind providing 60% of an annual consumption of 3300 kWh per household. This scaling needs checking. It may also be worth adding biogen and hydro from green tariffs here too, plus the option to simulate oversupply - used by ZCB to reduce backup requirement.</i></p>
    <table class="table">
    <tr><th>Name</th><th>Consumption</th><th>Onsite solar</th><th>Grid Wind<br>& Hydro</th><th>Balance</th><th style="text-align:center">gCO2/kWh</th></tr>
    <tbody id="users"></tbody>
    </table>
    
    <br>
    <div>Total generation: <span id="total_generation"></span>W</div>
    <div>Total consumption: <span id="total_consumption"></span>W</div>
    <div><span id="total_balance_text"></span><span id="total_balance"></span>W</div>
    
    <p>Virtual grid CO2 if one of the following added to current demand:<span style="font-size:95%">
    <br>3.1kW Immersion: <span id="add_immersion_co2"></span> gCO2/kWh, <span id="add_immersion_co2_vs_gas"></span>
    <br>2.3kW Electric car: <span id="add_ev_co2"></span> gCO2/kWh, <span id="add_ev_co2_mile"></span>
    <br>0.8kW Heatpump: <span id="add_heatpump_co2"></span> gCO2/kWh, <span id="add_heatpump_co2_vs_gas"></span></span>
    </p>
    
    <p>Marginal CO2 if one of the following added to current demand:<span style="font-size:95%">
    <br>3.1kW Immersion: <span id="add_immersion_co2_marginal"></span> gCO2/kWh, <span id="add_immersion_co2_vs_gas_marginal"></span>
    <br>2.3kW Electric car: <span id="add_ev_co2_marginal"></span> gCO2/kWh, <span id="add_ev_co2_mile_marginal"></span>
    <br>0.8kW Heatpump: <span id="add_heatpump_co2_marginal"></span> gCO2/kWh, <span id="add_heatpump_co2_vs_gas_marginal"></span></span>
    </p>
    
    <p style="font-size:80%;"><i>
    110 gCO2/km for ICE car based on: 10.6 kgCO2 per gallon <a href="http://www.carbonindependent.org/sources_car.html">[1]</a> and 60mpg (typical of the most efficient small car real world mpg <a href="http://www.whatcar.com/car-news/real-world-mpg-efficient-small-cars/1214063">[2])</a> Does not include 85% refining and extraction efficiency which would add another 1.9 kgCO2 per gallon increasing this to 130gCO2/km - there are likely related losses for grid fossil fuels too [needs checking].<br>EV efficiency is based on 3.8 miles/kWh including charging loss.<br><br>Gas intensity based on 230gCO2/kWh heat delivered <a href="http://heatpumps.co.uk/ecology.htm">[3]</a>.<br>Heatpump heat carbon intensity based on a COP of 3.0.
    </i></p>
    <!--<div>Total group cost: <span id="grouptotal"></span>p</div>
    <br>
    <p>The energy cost is accumulating once the window is left open, if a user uses more power than is available from onsite solar and their share of uk wind that power is charged and distributed to users who are using under their available balance.</p>
    
    <p>The users energy bill increases by 15p/kWh imported and decreases by 15p/kWh surplus on a user by user basis.</p>-->
</div>

<div class="box-dark" style="width:1000px; height:900px; float:left;">

  <div style="float:left"><b>Detailed View</b></div>
  
  <div class='btn-group' style="float:right">
      <button id='zoomin' class='btn' >+</button>
      <button id='zoomout' class='btn' >-</button>
      <button id='left' class='btn' ><</button>
      <button id='right' class='btn' >></button>
  </div>

  <div class='btn-group' style="float:right; padding-right:10px">
      <button class='btn time visnav' type='button' time='1'>D</button>
      <button class='btn time' type='button' time='7'>W</button>
      <button class='btn time' type='button' time='30'>M</button>
      <button class='btn time' type='button' time='365'>Y</button>
  </div>
  
  <div style="clear:both"></div><br>

  <div id="placeholder_bound">
      <div id="placeholder"></div>
  </div>
  
  <br>
  <div id="out" style="font-size:90%; color:#ccc"></div>
  
  <p  style="font-size:90%; color:#ccc"><b>Axis</b> 1:Total CO2 (g), 2:Intensity (gCO2/kWh), 3: Power (Watts)</p>
</div>

<div class="box" style="width:600px; float:left;">
  <h3>Grid Wind Forecast</h3>

  <div id="forecast_placeholder_bound">
      <div id="forecast_placeholder"></div>
  </div>
</div>

<div class="box" style="width:600px; float:left;">
  <h3>Supply Mix Selection</h3>
  
  <table><tr><td>
  
  <p><b>Share of renewables</b></p>
  <p>Typical household demand: 3300 kWh</p>
  <p>kWh of grid wind: <input id="kwh_of_grid_wind" type="text" value="1980" style="width:150px" autocomplete="off" /><span id="wind_prc"></span></p>
  <p>kWh of grid hydro: <input id="kwh_of_grid_hydro" type="text" value="660" style="width:150px" autocomplete="off" /><span id="hydro_prc"></span></p>
  
  </td><td>
  
  <p><b>Include in Grid CO2 Intensity</b></p>
  <p>
  <br>Nuclear<input type="checkbox" id="inc_nuclear" checked autocomplete="off" />
  <br>Coal<input type="checkbox" id="inc_coal" checked autocomplete="off" />
  <br>Gas (CCGT)<input type="checkbox" id="inc_gas" checked autocomplete="off" />
  <br>Interconnectors<input type="checkbox" id="inc_inter" checked autocomplete="off" />
  <br>Other<input type="checkbox" id="inc_other" checked autocomplete="off" />
  <br>Renewables<input type="checkbox" id="inc_renewables" autocomplete="off" /> (may be double counting)
  </p>
  
  </td></tr>
  </table>
  
  <button id="mixrecalculate">Recalculate</button>
</div>

<div class="box" style="width:600px; float:left;">
  <h3>Associated tools</h3>
  <p><a href="http://openenergymonitor.org/ukgrid">UK Electricity Grid Supply View</a></p>
  <p><a href="http://openenergymonitor.org/energymodel/">10 year hourly zero carbon energy system model</a></p>

  
</div>

<script>

var path = "https://openenergymonitor.org/dev/smartgrid/";

// ------------------------------------------------------------------------
// DRAW OPTIONS TABLE + UI CONTROL
// ------------------------------------------------------------------------
var smartgrid = {};
var checkboxstate = {};

update();
setInterval(update,10000);

function update(){

    smartgrid = {};

    $.ajax({                                      
        url: path+"api.php?q=smartgrid&auth=AUTHENTICATION_KEY",
        async: false,
        dataType: "json",
        success: function(result) {
            smartgrid = result;
        }
    });
    
    // ------------------------------------------------------------------------------
    var gridvalues = load_grid_lastvalue();

    var intensityall = {
      "CCGT":360,
      "OCGT":480,
      "COAL":910,
      "NUCLEAR":0,
      "WIND":0,
      //"PS":0,
      "NPSHYD":0,
      "OTHER":300,
      "OIL":610,
      "INTFR":90,
      "INTIRL":450,
      "INTNED":550,
      "INTEW":450
    };
    
    var gridco2intensity = 0;
    var griddemand = 0;
    for (var z in intensityall) {
        var value = 1.0 * gridvalues[z];
        gridco2intensity += value * intensityall[z];
        griddemand += value;
    }
    gridco2intensity = gridco2intensity / griddemand; // normalise
    gridco2intensity = gridco2intensity / 0.93;       // grid losses 7%
    $("#co2").html(Math.round(gridco2intensity));
    
    var intensity = {};
    
    if ($("#inc_renewables")[0].checked) {
        intensity["WIND"] = 0;
        intensity["NPSHYD"] = 0;
    }
    
    if ($("#inc_nuclear")[0].checked) intensity["NUCLEAR"] = 0;
    
    if ($("#inc_coal")[0].checked) intensity["COAL"] = 910;
    
    if ($("#inc_gas")[0].checked) {
        intensity["CCGT"] = 360;
        intensity["OCGT"] = 480;
    }
    
    if ($("#inc_inter")[0].checked) {
        intensity["INTFR"] = 90;
        intensity["INTIRL"] = 450;
        intensity["INTNED"] = 550;
        intensity["INTEW"] = 450;
    }
    
    if ($("#inc_other")[0].checked) {
        intensity["OTHER"] = 300;
        intensity["OIL"] = 610;
    }

    var marginalgridco2intensity = 0;
    var griddemand = 0;
    for (var z in intensity) {
        var value = 1.0 * gridvalues[z];
        marginalgridco2intensity += value * intensity[z];
        griddemand += value;
    }
    marginalgridco2intensity = marginalgridco2intensity / griddemand; // normalise
    marginalgridco2intensity = marginalgridco2intensity / 0.93;       // grid losses 7%
    
    $("#marginalco2").html(Math.round(marginalgridco2intensity));
    // ------------------------------------------------------------------------------ 

    // WIND
        var average_wind_power = 2650; // MW
        var average_power = (($("#kwh_of_grid_wind").val()/365.0)/0.024); // 375W
        var wind_now = Math.round((average_power / average_wind_power) * gridvalues['WIND']);
    
    // Hydro
        var average_hydro_power = 687; // MW
        var average_power = (($("#kwh_of_grid_hydro").val()/365.0)/0.024); // 375W
        var hydro_now = Math.round((average_power / average_hydro_power) * gridvalues['NPSHYD']);
        

    var correction_factor = 345.0 / marginalgridco2intensity;
    console.log("Correction factor: "+correction_factor);
    var corrected_renewable = (gridvalues['WIND']+smartgrid['ukgrid']['solar']) * correction_factor;
    console.log("Corrected renewable: "+corrected_renewable);

    var status = "amber";
    if (corrected_renewable>4000) status = "green";
    if (corrected_renewable<3000) status = "red";

    if (status=="red") {
        $("#status").css("background-color","rgb(255,50,50)");
        $("#status").html("UK grid status: Red, high CO2, low renewable");
    }

    if (status=="green") {
        $("#status").css("background-color","rgb(50,255,0)");
        $("#status").html("UK grid status: low CO2, high renewable");
    }

    if (status=="amber") {
        $("#status").css("background-color","rgb(255,200,50)");
        $("#status").html("UK grid status: mid CO2, mid renewable");
    }

    $("#ukwind").html(Math.round(gridvalues['WIND']));
    $("#uksolar").html(Math.round(smartgrid['ukgrid']['solar']));
    $("#ukhydro").html(Math.round(gridvalues['NPSHYD']));

    console.log(status);

    var total_balance = 0;
    var total_consumption = 0;
    var total_generation = 0;

    var out = "";
    for (var z in smartgrid.users) {
        out += "<tr><td>"+z+"</td>";
        
        if (smartgrid.users[z].consumption.value<0) smartgrid.users[z].consumption.value = 0;
        out += "<td><input class='use' user='"+z+"' type='checkbox'/ > "+smartgrid.users[z].consumption.value+"W</td>";
        
        if (smartgrid.users[z].solar.value<5) smartgrid.users[z].solar.value = 0;
        out += "<td><input class='solar' user='"+z+"' type='checkbox'/ > "+smartgrid.users[z].solar.value+"W</td>";
        out += "<td><input class='wind' user='"+z+"' type='checkbox'/ > "+(wind_now+hydro_now)+"W</td>";
        
        var supply = smartgrid.users[z].solar.value + wind_now + hydro_now;
        var demand = smartgrid.users[z].consumption.value;
        var balance = supply - demand;
        out += "<td>"+balance+"W</td>";
        
        //if (financial[z]==undefined) financial[z] = 0;
        //financial[z] += -1*balance * (15.0/3600000.0) * 10;
        //out += "<td>"+financial[z].toFixed(3)+"p</td>";
        var importing = 0;
        if (balance<0) importing = balance * -1;
        var household_co2 = marginalgridco2intensity * (importing / demand);
        
        var color = "rgb(255,200,50)";
        if (household_co2<10.0) color = "rgb(50,255,0)";
        if (household_co2>150.0) color = "rgb(255,50,50)";    
        
        out += "<td style='padding:2px; text-align:center; background-color:"+color+"'>"+household_co2.toFixed(0)+"</td>";
        
        out += "</tr>";
        
        total_balance += balance;
        total_consumption += smartgrid.users[z].consumption.value;
        total_generation += supply;
        
    }

    $("#users").html(out);

    // Turn all options on by default
    for (user in smartgrid.users) {
        if (checkboxstate[user]==undefined) checkboxstate[user] = {};
        if (checkboxstate[user].consumption==undefined) checkboxstate[user].consumption = true;
        if (checkboxstate[user].solar==undefined) checkboxstate[user].solar = true;
        if (checkboxstate[user].wind==undefined) checkboxstate[user].wind = true;
        
        smartgrid.users[user].consumption.show = checkboxstate[user].consumption;
        smartgrid.users[user].solar.show = checkboxstate[user].solar;
        smartgrid.users[user].wind = checkboxstate[user].wind;
        
        $(".use[user='"+user+"']")[0].checked = checkboxstate[user].consumption;
        $(".solar[user='"+user+"']")[0].checked = checkboxstate[user].solar;
        $(".wind[user='"+user+"']")[0].checked = checkboxstate[user].wind;
    }
    
    
    $("#total_consumption").html(total_consumption);
    $("#total_generation").html(total_generation);

    if (total_balance>=0) $("#total_balance_text").html("Currently Exporting: "); else $("#total_balance_text").html("Currently Importing: ");
    $("#total_balance").html(Math.abs(total_balance));

    // -------------------------------------------------------------------------------------------------
    // SMART GRID BALANCE AND INDICATOR
    // -------------------------------------------------------------------------------------------------
    var joules = 0;
    if (total_balance<0) joules = total_balance * -1 * 10.0;
    var gco2 = marginalgridco2intensity * (joules/3600000.0)
    var CO2 = gco2 / ((total_consumption*10)/3600000.0);

    var status = "amber";
    if (CO2<10.0) status = "green";
    if (CO2>150.0) status = "red";

    if (status=="red") {
        $("#group-status").css("background-color","rgb(255,50,50)");
        $("#group-status").html("Virtual microgrid status: Red, high CO2, "+Math.round(CO2)+" gCO2/kWh");
    }

    if (status=="green") {
        $("#group-status").css("background-color","rgb(50,255,0)");
        $("#group-status").html("Virtual microgrid status: Green, low CO2, "+Math.round(CO2)+" gCO2/kWh");
    }

    if (status=="amber") {
        $("#group-status").css("background-color","rgb(255,200,50)");
        $("#group-status").html("Virtual microgrid status: Amber, mid CO2, "+Math.round(CO2)+" gCO2/kWh");
    }

    // -------------------------------------------------------------------------------------------------
    // SMART GRID DEMAND INTENSITY EXAMPLES
    // -------------------------------------------------------------------------------------------------
    var power = 2300;
    var tmp_total_balance = total_balance - power;
    var importing = 0;
    if (tmp_total_balance<0) importing = tmp_total_balance * -1;
    var tmp_co2 = marginalgridco2intensity * (importing / (total_consumption+power));
    $("#add_ev_co2").html(Math.round(tmp_co2));
    $("#add_ev_co2_mile").html(Math.round(tmp_co2/(3.8*1.6))+"gCO2/km");
    
    var tmp_total_balance = total_balance - 3100;
    var importing = 0;
    if (tmp_total_balance<0) importing = tmp_total_balance * -1;
    var tmp_co2 = marginalgridco2intensity * (importing / (total_consumption+3100));
    $("#add_immersion_co2").html(Math.round(tmp_co2));
    $("#add_immersion_co2_vs_gas").html(Math.round(100*tmp_co2/230.0)+"% of gas");
    
    var tmp_total_balance = total_balance - 800;
    var importing = 0;
    if (tmp_total_balance<0) importing = tmp_total_balance * -1;
    var tmp_co2 = marginalgridco2intensity * (importing / (total_consumption+800));
    $("#add_heatpump_co2").html(Math.round(tmp_co2));
    $("#add_heatpump_co2_vs_gas").html(Math.round(tmp_co2/3.0)+"gCO2/kWh heat, "+Math.round(100*(tmp_co2/3.0)/230.0)+"% of gas");
    
    // -------------------------------------------------------------------------------------------------
    // SMART GRID DEMAND INTENSITY EXAMPLES - based on marginal rate
    // -------------------------------------------------------------------------------------------------
    var power = 2300;
    var additional_import = power; 
    if (total_balance>0) additional_import = power - total_balance;
    if (additional_import<0) additional_import = 0;
    var tmp_co2 = marginalgridco2intensity * (additional_import / power);
    $("#add_ev_co2_marginal").html(Math.round(tmp_co2));
    $("#add_ev_co2_mile_marginal").html(Math.round(tmp_co2/(3.8*1.6))+"gCO2/km");
    
    var power = 3100;
    var additional_import = power; 
    if (total_balance>0) additional_import = power - total_balance;
    if (additional_import<0) additional_import = 0;
    var tmp_co2 = marginalgridco2intensity * (additional_import / power);
    $("#add_immersion_co2_marginal").html(Math.round(tmp_co2));
    $("#add_immersion_co2_vs_gas_marginal").html(Math.round(100*tmp_co2/230.0)+"% of gas");
    
    var power = 800;
    var additional_import = power; 
    if (total_balance>0) additional_import = power - total_balance;
    if (additional_import<0) additional_import = 0;
    var tmp_co2 = marginalgridco2intensity * (additional_import / power);
    $("#add_heatpump_co2_marginal").html(Math.round(tmp_co2));
    $("#add_heatpump_co2_vs_gas_marginal").html(Math.round(tmp_co2/3.0)+"gCO2/kWh heat, "+Math.round(100*(tmp_co2/3.0)/230.0)+"% of gas");
}

$("body").on("click",".use",function() {
    var user = $(this).attr("user");
    checkboxstate[user].consumption = $(this)[0].checked;
    smartgrid.users[user].consumption.show = checkboxstate[user].consumption;
    load();
});

$("body").on("click",".solar",function() {
    var user = $(this).attr("user");
    checkboxstate[user].solar = $(this)[0].checked;
    smartgrid.users[user].solar.show = checkboxstate[user].solar;
    load();
});

$("body").on("click",".wind",function() {
    var user = $(this).attr("user");
    checkboxstate[user].wind = $(this)[0].checked;
    smartgrid.users[user].wind = checkboxstate[user].wind;
    load();
});

$("#mixrecalculate").click(function(){
    load();
    update();
    $("#wind_prc").html(" "+Math.round(100*($("#kwh_of_grid_wind").val()/3300.0))+"%");
    $("#hydro_prc").html(" "+Math.round(100*($("#kwh_of_grid_hydro").val()/3300.0))+"%");
});

$("input[type=checkbox]").click(function(){
update();
});
    
// ------------------------------------------------------------------------
// GRAPH WIDTH/HEIGHT
// ------------------------------------------------------------------------
var placeholder_bound = $('#placeholder_bound');
var placeholder = $('#placeholder');

var width = placeholder_bound.width();
var height = width * 0.6;

placeholder.width(width);
placeholder_bound.height(height);
placeholder.height(height);

// ------------------------------------------------------------------------
// START WINDOW
// ------------------------------------------------------------------------

var period = 3600*24;
var interval = 30;
var npoints = period / interval;
var timenow = (new Date()).getTime()*0.001;
view.end = Math.floor(timenow/interval)*interval;
view.start = view.end - period;

var solar_data = [];
var consumption_data = [];
var grid = {};

reload();

// ------------------------------------------------------------------------
// RELOAD FUNCTION 
// This is called when we need to select a new view and reload the data
// ------------------------------------------------------------------------
function reload()
{
    interval = view.calc_interval(1000);
    view.end = Math.floor(view.end/interval)*interval;
    view.start = Math.floor(view.start/interval)*interval;
    
    period = view.end - view.start;
    npoints = period / interval;

    var data = [];

    $.ajax({                                      
        url: path+"api.php?q=data&id=1&start="+(view.start*1000)+"&end="+(view.end*1000)+"&interval="+interval+"&skipmissing=0&limitinterval=0&auth=AUTHENTICATION_KEY",
        async: false,
        dataType: "json",
        success: function(rx) {
            data = rx;
        }
    });

    grid = load_grid_data(view.start,view.end,interval);
    
    console.log(grid['WIND']);

    // ----------------------------------------------------------------------
    // TRANSLATE BULK DATA OBJECT INTO INDIVIDUAL CONSUMPTION AND SOLAR FEEDS
    // ----------------------------------------------------------------------
    solar_data = [];
    consumption_data = [];
    
    var time = view.start;
    for (var row=0; row<npoints; row++) {
        time = view.start + (interval*row);

        var n = 2;
        for (var z in smartgrid.users) {

            if (smartgrid.users[z].consumption.id!=0) {
                if (consumption_data[z]==undefined) consumption_data[z] = [];
                consumption_data[z].push([time*1000,data[row][n]]);
                n++;
            }
            
            if (smartgrid.users[z].solar.id!=0) {
                if (solar_data[z]==undefined) solar_data[z] = [];
                solar_data[z].push([time*1000,data[row][n]]);
                n++;
            }  
        }
    }

    load();
}

// ------------------------------------------------------------------------
// LOAD: 
// This is called when we need to draw or redraw the existing loaded dataset
// ------------------------------------------------------------------------
function load()
{
    var totalsolar = [];
    var totalconsumption = [];
    var totalwind = [];
    var totalhydro = [];
    var total_co2_data = [];
    var total_co2_inc_displaced_data = [];
    var gridco2_data = [];
    
    var ukgridco2 = 350;
    var virtualgridco2 = [];
    var n = 0;

    var isolar = [];
    var iconsumption = [];
    var iwind = 0;
    var ihydro = 0;

    var totaluse = 0;
    var totalgen = 0;
    var totalunmet = 0;
    var totalexess = 0;
    
    var total_co2 = 0;
    var total_co2_inc_displaced = 0;
    var gridco2intensity = 0;
    
    var intensity = {};
    
    if ($("#inc_renewables")[0].checked) {
        intensity["WIND"] = 0;
        intensity["NPSHYD"] = 0;
    }
    
    if ($("#inc_nuclear")[0].checked) intensity["NUCLEAR"] = 0;
    
    if ($("#inc_coal")[0].checked) intensity["COAL"] = 910;
    
    if ($("#inc_gas")[0].checked) {
        intensity["CCGT"] = 360;
        intensity["OCGT"] = 480;
    }
    
    if ($("#inc_inter")[0].checked) {
        intensity["INTFR"] = 90;
        intensity["INTIRL"] = 450;
        intensity["INTNED"] = 550;
        intensity["INTEW"] = 450;
    }
    
    if ($("#inc_other")[0].checked) {
        intensity["OTHER"] = 300;
        intensity["OIL"] = 610;
    }

    for (var t=view.start; t<view.end; t+=interval) {

        var total_solar_gen = 0;
        var total_wind_gen = 0;
        var total_hydro_gen = 0;
        var total_consumption = 0;
        
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
        
        // SOLAR
        for (var u in solar_data) {
            if (smartgrid.users[u].solar.show) {
                if (isolar[u]==undefined) isolar[u] = 0;
                if (solar_data[u][n][1]!=null) isolar[u] = solar_data[u][n][1];
                total_solar_gen += isolar[u];
            }
        }
        if (total_solar_gen<0) total_solar_gen = 0;
        // CONSUMPTION
        for (var u in consumption_data) {
            if (smartgrid.users[u].consumption.show) {
                if (iconsumption[u]==undefined) iconsumption[u] = 0;
                if (consumption_data[u][n][1]!=null) iconsumption[u] = consumption_data[u][n][1];
                total_consumption += iconsumption[u];
            }
            
            // WIND
            if (smartgrid.users[u].wind) {
                var average_wind_power = 2650; // MW
                var average_power = (($("#kwh_of_grid_wind").val()/365.0)/0.024); // 375W
                var wind_now = Math.round((average_power / average_wind_power) * iwind);
                total_wind_gen += wind_now;
            }
            
            // Hydro
            if (smartgrid.users[u].wind) {
                var average_hydro_power = 687; // MW
                var average_power = (($("#kwh_of_grid_hydro").val()/365.0)/0.024); // 375W
                var hydro_now = Math.round((average_power / average_hydro_power) * ihydro);
                total_hydro_gen += hydro_now;
            }
            
        }
        
        // WIND, SOLAR, CONSUMPTION
        totalsolar.push([t*1000,total_hydro_gen+total_wind_gen+total_solar_gen]);
        totalconsumption.push([t*1000,total_consumption]);
        totalwind.push([t*1000,total_hydro_gen+total_wind_gen]);
        totalhydro.push([t*1000,total_hydro_gen]);
        gridco2_data.push([t*1000,gridco2intensity]);
        
        var supply = total_wind_gen+total_solar_gen+total_hydro_gen;
        var demand = total_consumption;
        
        var balance = supply - demand;
        
        var imported = 0;
        var exess = 0;
        if (balance<0) {
            imported = balance * -1;
        }
        if (balance>0) exess = balance;
        
        var co2 = gridco2intensity * (imported/total_consumption);
        
        total_co2 += (interval * imported / 3600000.0) * gridco2intensity
        total_co2_data.push([t*1000,total_co2]);
        
        total_co2_inc_displaced += (interval * (-1*balance) / 3600000.0) * gridco2intensity;
        total_co2_inc_displaced_data.push([t*1000,total_co2_inc_displaced]);
        
        virtualgridco2.push([t*1000,co2]);
        
        totaluse += (demand * interval) / 3600000.0;
        totalgen += (supply * interval) / 3600000.0;
        totalunmet += (imported * interval) / 3600000.0;
        totalexess += (exess * interval) / 3600000.0;
        n++;
    }

    var out = "";
    out += "<b>Total Consumption:</b> "+(totaluse).toFixed(0)+" kWh, ";
    out += "<b>Total Generation:</b> "+(totalgen).toFixed(0)+" kWh, ";
    out += "<b>Total Unmet:</b> "+(totalunmet).toFixed(0)+" kWh, ";
    out += "<b>Total Exess:</b> "+(totalexess).toFixed(0)+" kWh, ";
    
    var matching = (1.0 - (totalunmet / totaluse))*100;
    out += "<b>Matching:</b> "+(matching).toFixed(0)+"%<br><br>";
    out += "<b>Total CO2:</b> "+(total_co2).toFixed(0)+"g ";
    out += "<b>Average virtual grid CO2 intensity:</b> "+(total_co2/totaluse).toFixed(0)+"gCO2/kWh ";
    $("#out").html(out);

    var series = [];
    series.push({label:"Solar", data:totalsolar, yaxis:1, color: "#dccc1f", lines:{show:true, fill:1.0, lineWidth: 0}});
    series.push({label:"Wind", data:totalwind, yaxis:1, color: "#2ed52e", lines:{show:true, fill:1.0, lineWidth: 0}});
    series.push({label:"Hydro", data:totalhydro, yaxis:1, color: "#3333cc", lines:{show:true, fill:1.0, lineWidth: 0}});
    series.push({label:"Consumption", data:totalconsumption, yaxis:1, color: "#0699fa", lines:{show:true, fill:0.6, lineWidth: 0}});
    
    series.push({label:"Virtual Grid CO2", data:virtualgridco2, yaxis:2, color: "#ff5500", lines:{show:true, fill:false, lineWidth: 1}});
    series.push({label:"Grid CO2", data:gridco2_data, yaxis:2, color: "#ff0000", lines:{show:true, fill:false, lineWidth: 1}});
    series.push({label:"Total CO2", data:total_co2_data, yaxis:3, color: "#aaa", lines:{show:true, fill:false, lineWidth: 1}});
    series.push({label:"Total CO2 inc displaced", data:total_co2_inc_displaced_data, yaxis:3, color: "#888", lines:{show:true, fill:false, lineWidth: 1}});

    var options = {
      xaxis: {
          min: view.start*1000, max: view.end*1000, mode: "time", timezone: "browser"
      },
      selection: { mode: "x", color:"#000" },
      legend: {show:true, position: "nw"}
    };
  
    $.plot("#placeholder",series, options);
}

// ------------------------------------------------------------------------
// VIEW CONTROLS:
// ------------------------------------------------------------------------
$('#placeholder').bind("plotselected", function (event, ranges) {
    view.start = ranges.xaxis.from*0.001;
    view.end = ranges.xaxis.to*0.001;
    reload();
});

$("#zoomout").click(function () {view.zoomout(); reload();});
$("#zoomin").click(function () {view.zoomin(); reload();});
$('#right').click(function () {view.panright(); reload();});
$('#left').click(function () {view.panleft(); reload();});
$('.time').click(function () {view.timewindow($(this).attr("time")); reload();});


// ------------------------------------------------------------------------
// FORECAST:
// ------------------------------------------------------------------------

var timenow = (new Date()).getTime();

var forecast_placeholder_bound = $('#forecast_placeholder_bound');
var forecast_placeholder = $('#forecast_placeholder');

var width = forecast_placeholder_bound.width();
var height = width * 0.65;

forecast_placeholder.width(width);
forecast_placeholder_bound.height(height);
forecast_placeholder.height(height);

var data = [];
$.ajax({                                      
    url: path+"getwindforecast.php",
    async: false,
    dataType: "json",
    success: function(rx) {
        data = rx;
    }
});

var s1 = [];
var s2 = [];
var s3 = [];
var markings = [];

for (z in data) {
    if (data[z][1]!=null) s1.push([data[z][0]*1000,data[z][1]]);
    if (data[z][2]!=null) s2.push([data[z][0]*1000,data[z][2]]);
    if (data[z][3]!=null) s3.push([data[z][0]*1000,data[z][3]]);
    
    if (data[z][0]%86400==0) markings.push({ color: "#ccc", lineWidth: 1, xaxis: { from: data[z][0]*1000, to: (data[z][0]+3600*6)*1000 } });
    if (data[z][0]%86400==0) markings.push({ color: "#000", lineWidth: 1, xaxis: { from: data[z][0]*1000, to: data[z][0]*1000 } });
}

var series = []
series.push({label:"First Forecast", data:s1});
series.push({label:"Second Forecast", data:s2});
series.push({label:"Actual Output", data:s3});


var options = {
  xaxis: {
      // min: view.start, max: view.end
      mode: "time", timezone: "browser", tickSize: [3, "hour"], timeformat: "%h"
  },
  yaxis: {
      min:0, max:6000
  },
  selection: { mode: "x", color:"#000" },
  legend: {show:true},
  grid: { markings: markings }
};

var plot = $.plot("#forecast_placeholder",series, options);

//var o = plot.pointOffset({ x: 2, y: -1.2});
//placeholder.append("<div style='position:absolute;left:" + (o.left + 4) + "px;top:" + o.top + "px;color:#666;font-size:smaller'>Warming up</div>");

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
  
  console.log(grid);
  
  return grid;
}
</script>
