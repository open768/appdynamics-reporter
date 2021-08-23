<?php

/**************************************************************************
Copyright (C) Chicken Katsu 2013-2021 

This code is protected by copyright under the terms of the 
Creative Commons Attribution-NonCommercial-NoDerivatives 4.0 International License
http://creativecommons.org/licenses/by-nc-nd/4.0/legalcode

For licenses that allow for commercial use please contact cluck@chickenkatsu.co.uk

// USE AT YOUR OWN RISK - NO GUARANTEES OR ANY FORM ARE EITHER EXPRESSED OR IMPLIED
**************************************************************************/

//####################################################################
$home="../..";
require_once "$home/inc/common.php";
require_once "$root/inc/inc-charts.php";

$sMetricType = cHeader::get(cRender::METRIC_TYPE_QS);
switch($sMetricType){
	case cAppDynMetric::METRIC_TYPE_RUMCALLS:
	case cAppDynMetric::METRIC_TYPE_RUMRESPONSE:
		$sTitle1 = "Web Browser Page Requests";
		$sMetric1 = cAppDynWebRumMetric::CallsPerMin();
		$sTitle2 = "Web Browser Page Response";
		$sMetric2 = cAppDynWebRumMetric::ResponseTimes();
		$sTitle3 = "Pages With Javascript Errors";
		$sMetric3 = cAppDynWebRumMetric::JavaScriptErrors();
		
		$sBaseUrl = "$home/pages/rum/apprum.php";
		break;
	case cAppDynMetric::METRIC_TYPE_RESPONSE_TIMES:
	case cAppDynMetric::METRIC_TYPE_ACTIVITY:
	default:
		$sTitle1 = "Application Activity";
		$sMetric1 = cAppDynMetric::appCallsPerMin();
		$sTitle2 = "Application Response Times";
		$sMetric2 = cAppDynMetric::appResponseTimes();
		$sTitle3 = "Application Errors";
		$sMetric3 = cAppDynMetric::appErrorsPerMin();
		$sBaseUrl = "$home/pages/app/tiers.php";
		break;
}

//####################################################################
cRenderHtml::header("All Applications - $sTitle1");
cRender::force_login();
cChart::do_header();
cChart::$hideGroupIfNoData = true;

//####################################################################
cRender::show_time_options( "All Applications - $sTitle1"); 		
cRender::appdButton(cAppDynControllerUI::apps_home());

//####################################################################
//this should be done asynchronously
$aResponse = cAppDynController::GET_Applications();
if ( count($aResponse) == 0)
	cRender::messagebox("Nothing found");
else{
	cDebug::write( count($aResponse). " applications found");
	//display the results
	foreach ( $aResponse as $oApp){
		if (cFilter::isAppFilteredOut($oApp)) continue;
		$sUrl = cHttp::build_url($sBaseUrl, cRenderQS::get_base_app_QS($oApp));
		$aMetrics = [
			[cChart::LABEL=>$sTitle1, cChart::METRIC=>$sMetric1, cChart::GO_URL=>$sUrl, cChart::GO_HINT=>"detail for $oApp->name", ],
			[cChart::LABEL=>$sTitle2, cChart::METRIC=>$sMetric2],
			[cChart::LABEL=>$sTitle3, cChart::METRIC=>$sMetric3]
		];
		cRenderMenus::show_app_functions($oApp);
		
		cChart::render_metrics($oApp, $aMetrics,cChart::CHART_WIDTH_LETTERBOX/3);
		cDebug::flush();
		if (cDebug::is_extra_debugging()) {
			cDebug::vardump($oApp);	
			break;	//DEBUG
		}
		?><br><?php
		cCommon::flushprint("");
	}
}
cChart::do_footer();
cRenderHtml::footer();
?>
