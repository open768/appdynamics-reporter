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
require_once("$phpinc/ckinc/header.php");
require_once("$phpinc/ckinc/http.php");
	
cSession::set_folder();
session_start();
cDebug::check_GET_or_POST();

//####################################################################
require_once("$phpinc/appdynamics/appdynamics.php");
require_once("$phpinc/appdynamics/common.php");
require_once("inc/inc-charts.php");
require_once("inc/inc-secret.php");
require_once("inc/inc-render.php");


const COLUMNS=6;
error_reporting(E_ALL);

//display the results
$app = cHeader::get(cRender::APP_QS);
$fromtier = cHeader::get(cRender::FROM_TIER_QS);
$totier = cHeader::get(cRender::TO_TIER_QS);
$gsTierQS = cRender::get_base_tier_qs();

//####################################################################
cRender::html_header("External tier calls");
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


//####################################################################
$title =  "$app&gt;$fromtier&gt; to tier $totier";		
cRender::show_time_options($title); 
?>
<h2>Tier activity details<h2>
<h3>from (<?=$fromtier?>) to (<?=$totier?>)</h3>
<p>
<?php
	cRender::button("back to ($fromtier) external tiers", cHttp::build_url("tierextgraph.php", $gsTierQS));
?>
<table class="maintable">
	<tr class="<?=cRender::getRowClass()?>"><td>
	<?php
		$sMetricUrl = cAppDynMetric::tierExtCallsPerMin($fromtier, $totier);
		cChart::add("Calls per min from ($fromtier) to ($totier)", $sMetricUrl, $app);
	?>
	</td></tr>
	<tr class="<?=cRender::getRowClass()?>"><td>
	<?php
		$sMetricUrl = cAppDynMetric::tierExtResponseTimes($fromtier, $totier);
		cChart::add("Response Times in ms from ($fromtier) to ($totier)", $sMetricUrl, $app);
	?>
	</td></tr>
</table>
<?php

//####################################################################
//################ CHART
cChart::do_footer("chart_getUrl", "chart_jsonCallBack");

cRender::html_footer();
?>