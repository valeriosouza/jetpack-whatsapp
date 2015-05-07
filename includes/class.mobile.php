<?php 

$iPod = false;
$iPhone = false;
$iPad = false;
$iOS = false;
$webOSPhone = false;
$webOSTablet = false;
$webOS = false;
$BlackBerry9down = false;
$BlackBerry10 = false;
$RimTablet = false;
$BlackBerry = false;
$NokiaSymbian = false;
$Symbian = false;
$AndroidTablet = false;
$AndroidPhone = false;
$Android = false;
$WindowsPhone = false;
$WindowsTablet = false;
$Windows = false;
$Tablet = false;
$Phone = false;

if(stripos($_SERVER['HTTP_USER_AGENT'],"iPod")){
    $iPod = true;
    $Phone = true;
    $iOS = true;
}
if(stripos($_SERVER['HTTP_USER_AGENT'],"iPhone")){
    $iPhone = true;
    $Phone = true;
    $iOS = true;
}
if(stripos($_SERVER['HTTP_USER_AGENT'],"iPad")){
    $iPad = true;
    $Tablet = true;
    $iOS = true;
}

if(stripos($_SERVER['HTTP_USER_AGENT'],"Android")){

    $Android = true;

    if(stripos($_SERVER['HTTP_USER_AGENT'],"mobile")){
        $AndroidPhone = true;
        $Phone = true;
    }else{
        $AndroidTablet = true;
        $Tablet = true;
    }
}