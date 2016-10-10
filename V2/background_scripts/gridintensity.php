<?php

function gridintensity()
{
    $grid = json_decode(file_get_contents("https://openenergymonitor.org/ukgrid/api.php?q=lastvalue&id=1"));

    $names = array("CCGT","OCGT","OIL","COAL","NUCLEAR","WIND","PS","NPSHYD","OTHER","INTFR","INTIRL","INTNED","INTEW");

    $i=0;
    foreach ($names as $name){
        $gridvalues[$name] = $grid->values[$i];
        $i++;
    }

    $intensities = array(
        "CCGT"=>360,
        "OCGT"=>480,
        "COAL"=>910,
        "NUCLEAR"=>0,
        "WIND"=>0,
        //"PS"=>0,
        "NPSHYD"=>0,
        "OTHER"=>300,
        "OIL"=>610,
        "INTFR"=>90,
        "INTIRL"=>450,
        "INTNED"=>550,
        "INTEW"=>450
    );

    $gridco2intensity_tmp = 0;
    $griddemand = 0;
    foreach ($intensities as $key=>$intensity){
        $gridco2intensity_tmp += $gridvalues[$key] * $intensity;
        $griddemand += $gridvalues[$key];
    }

    if ($griddemand>0 && $griddemand<70000) {
        $gridco2intensity_tmp = $gridco2intensity_tmp / $griddemand; // normalise
        $gridco2intensity_tmp = $gridco2intensity_tmp / 0.93;       // grid losses 7%
        $gridco2intensity = $gridco2intensity_tmp;
    }

    return $gridco2intensity;
}
