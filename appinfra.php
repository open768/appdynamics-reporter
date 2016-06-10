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
	<script type="text/javascript" src="js/chart.php"></script>
<?php
cChart::do_header();
cChart::$json_data_fn = "chart_getUrl";
cChart::$json_callback_fn = "chart_jsonCallBack";
cChart::$csv_url = "rest/getMetric.php";
cChart::$zoom_url = "metriczoom.php";
cChart::$save_fn = "save_fave_chart";

cChart::$compare_url = "compare.php";
cChart::$metric_qs = cRender::METRIC_QS;
cChart::$title_qs = cRender::TITLE_QS;
cChart::$app_qs = cRender::APP_QS;
cChart::$showZoom = false;
cChart::$showCSV = false;
cChart::$showSave = false;
cChart::$showCompare = false;

//####################################################################
//####################################################################
$gsApp = cHeader::get(cRender::APP_QS);
$giAid = cHeader::get(cRender::APP_ID_QS);
$gsAppQs = cRender::get_base_app_QS();

//####################################################################
//####################################################################
$sTitle ="Infrastructure Overview for $gsApp";
cRender::show_time_options( $sTitle); 

?>
<h2><?=$sTitle?></h2>
<script language="javascript">
	bean.on(cChartBean, CHART__NODATA_EVENT, onChartNoData);
	
	function onChartNoData(poData){
		var sCaption, iPos;
		
		sCaption = poData.oItem.caption;
		iPos = sCaption.indexOf("(");
		if (iPos !== -1) sCaption = sCaption.substr(0,iPos);
		
		$("#"+poData.oItem.chart).html("no data: " + sCaption );
	}
</script>
<?php
//####################################################################
cRender::show_apps_menu("Infrastructure Overview for $gsApp","appinfra.php");
?><p><?php

//####################################################################
$aTiers =cAppdyn::GET_Tiers($gsApp);
cChart::$width=cRender::CHART_WIDTH_LARGE/4;

foreach ($aTiers as $oTier){
	if (cFilter::isTierFilteredOut($oTier->name)) continue;
	
	cRender::show_tier_functions($oTier->name, $oTier->id);
	$aMetricTypes = cRender::getInfrastructureMetricTypes();

	$aMetrics = [];
	foreach ($aMetricTypes as $sMetricType){
		$oMetric = cRender::getInfrastructureMetric($oTier->name,null,$sMetricType);
		$aMetrics[] = [$oMetric->caption, $oMetric->metric];
	}
	
	$sClass = cRender::getRowClass();
	cRender::render_metrics_table($gsApp,$aMetrics,4,$sClass);
}

//####################################################################
//####################################################################
cChart::do_footer("chart_getUrl", "chart_jsonCallBack");
cRender::html_footer();
?>
