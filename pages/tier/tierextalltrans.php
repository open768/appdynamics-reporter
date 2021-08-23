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


//********************************************************************
if (cAppdyn::is_demo()){
	cRender::errorbox("function not support ed for Demo");
	cRenderHtml::footer();
	exit;
}

//-----------------------------------------------
$oTier = cRenderObjs::get_current_tier();
$oApp = $oTier->app;
$sExt = cHeader::get(cRender::BACKEND_QS);
$sAppQS = cRenderQS::get_base_app_QS($oApp);
$sTierQS = cRenderQS::get_base_tier_QS($oTier);

//####################################################################
$sTitle = "All transactions calling External Service: $sExt";
cRenderHtml::header($sTitle);
cRender::force_login();
cChart::do_header();
cRender::show_time_options( $sTitle); 


//####################################################################
cRenderMenus::show_tier_functions();
$sExtQS = cHttp::build_qs($sAppQS, cRender::BACKEND_QS, $sExt);
$sUrl = cHttp::build_url("appexttiers.php", $sExtQS);

//####################################################################
?>
<!-- ************************************************** -->
<h2>All Calls from <?=cRender::show_name(cRender::NAME_TIER,$oTier)?> to <?=cRender::show_name(cRender::NAME_EXT,$sExt)?></h2>
<?php
$aMetrics = [];
$aMetrics[] = [cChart::LABEL=>"$oTier->name - Calls per min",cChart::METRIC=>cAppDynMetric::tierExtCallsPerMin($oTier->name,$sExt)];
$aMetrics[] = [cChart::LABEL=>"$oTier->name - Response time in ms", cChart::METRIC=>cAppDynMetric::tierExtResponseTimes($oTier->name,$sExt)];
cChart::metrics_table($oApp, $aMetrics,2,cRender::getRowClass());

//####################################################################
?>
<p>
<h2>All Transactions in <?=cRender::show_name(cRender::NAME_TIER,$oTier)?> calling <?=cRender::show_name(cRender::NAME_EXT,$sExt)?></h2>
<?php
//-----------------------------------------------


$aTrans = $oTier->GET_transaction_names();

$aMetrics = [];
foreach ( $aTrans as $oTrans){
	$sUrl = cHttp::build_qs($sTierQS, cRender::TRANS_QS, $oTrans->name);
	$sUrl = cHttp::build_qs($sUrl, cRender::TRANS_ID_QS, $oTrans->id);
	$sUrl = cHttp::build_url("$home/pages/trans/transdetails.php", $sUrl);
	
	$aMetrics[] = [
		cChart::LABEL=>"$oTrans->name - Calls per min from Tier", 
		cChart::METRIC=>cAppDynMetric::transCallsPerMin($oTier->name,$oTrans->name),
		cChart::HIDEIFNODATA=>1
	];
	$aMetrics[] = [
		cChart::LABEL=>"$oTrans->name to External - Calls per min ",
		cChart::METRIC=>cAppDynMetric::transExtCalls($oTier->name,$oTrans->name, $sExt),
		cChart::GO_URL => $sUrl,
		cChart::GO_HINT => "Transaction",
		cChart::HIDEIFNODATA=>1
	];
	$aMetrics[] = [
		cChart::LABEL=>"$oTrans->name to External - Response time in ms", 
		cChart::METRIC=>cAppDynMetric::transExtResponseTimes($oTier->name,$oTrans->name,$sExt),
		cChart::HIDEIFNODATA=>1
	];
	$aMetrics[] = [
		cChart::LABEL=>"$oTrans->name to External - errors", 
		cChart::METRIC=>cAppDynMetric::transExtErrors($oTier->name,$oTrans->name,$sExt),
		cChart::HIDEIFNODATA=>1
	];
}
cChart::metrics_table($oApp, $aMetrics,4,cRender::getRowClass());
cChart::do_footer();

cRenderHtml::footer();
?>
