<?php

/**************************************************************************
Copyright (C) Chicken Katsu 2013-2016 

This code is protected by copyright under the terms of the 
Creative Commons Attribution-NonCommercial-NoDerivatives 4.0 International License
http://creativecommons.org/licenses/by-nc-nd/4.0/legalcode

For licenses that allow for commercial use please contact cluck@chickenkatsu.co.uk

// USE AT YOUR OWN RISK - NO GUARANTEES OR ANY FORM ARE EITHER EXPRESSED OR IMPLIED
**************************************************************************/

//####################################################################
$root=realpath(".");
$phpinc = realpath("$root/../phpinc");
$jsinc = "../jsinc";

require_once("$phpinc/ckinc/debug.php");
require_once("$phpinc/ckinc/session.php");
require_once("$phpinc/ckinc/common.php");
require_once("$phpinc/ckinc/http.php");
require_once("$phpinc/ckinc/header.php");
	
cSession::set_folder();
session_start();
cDebug::check_GET_or_POST();

//####################################################################
require_once("$phpinc/appdynamics/appdynamics.php");
require_once("$phpinc/appdynamics/common.php");
require_once("inc/inc-charts.php");
require_once("inc/inc-secret.php");
require_once("inc/inc-render.php");

//####################################################################
cRender::html_header("Infrastructure");
cRender::force_login();
?>
	<script type="text/javascript" src="js/remote.js"></script>
	
<?php
cChart::$show_export_all = false;
cChart::do_header();

//####################################################################
//####################################################################
$gsApp = cHeader::get(cRender::APP_QS);
$giAid = cHeader::get(cRender::APP_ID_QS);
$oApp = cRender::get_current_app();
$gsAppQs = cRender::get_base_app_QS();

//####################################################################
//####################################################################
$sTitle ="Infrastructure Overview for $gsApp";
cRender::show_time_options( $sTitle); 

	//********************************************************************
	if (cAppdyn::is_demo()){
		cRender::errorbox("function not support ed for Demo");
		exit;
	}
	//********************************************************************

?>
<h2><?=$sTitle?></h2>
<?php
//####################################################################
cRenderMenus::show_apps_menu("Infrastructure","appinfra.php");
?><p><?php

//####################################################################
$aTiers =cAppdyn::GET_Tiers($gsApp);

foreach ($aTiers as $oTier){
	if (cFilter::isTierFilteredOut($oTier->name)) continue;
	
	cRenderMenus::show_tier_functions($oTier->name, $oTier->id);
	$aMetricTypes = cRender::getInfrastructureMetricTypes();

	$aMetrics = [];
	foreach ($aMetricTypes as $sMetricType){
		$oMetric = cRender::getInfrastructureMetric($oTier->name,null,$sMetricType);
		$aMetrics[] = [cChart::LABEL=>$oMetric->caption, cChart::METRIC=>$oMetric->metric];
	}
	
	$sClass = cRender::getRowClass();
	cChart::metrics_table($oApp,$aMetrics,4,$sClass);
}

//####################################################################
//####################################################################
cChart::do_footer();
cRender::html_footer();
?>
