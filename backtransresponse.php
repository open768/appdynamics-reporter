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

$SHOW_PROGRESS=false;
set_time_limit(200); 

//####################################################################
cRender::html_header("Backend Transaction Responses");
cRender::force_login();
?>
	<script type="text/javascript" src="js/remote.js"></script>
	
<?php
cChart::do_header();

//get passed in values
$app = cHeader::get(cRender::APP_QS);
$aid = cHeader::get(cRender::APP_ID_QS);
$backend = cHeader::get(cRender::BACKEND_QS);
$sAppQS = cRender::get_base_app_QS();
$sBackendQS = cHttp::build_QS($sAppQS, cRender::BACKEND_QS, $backend);

$title= "$app&gt;Backend Transaction response times&gt;$backend";
cRender::show_time_options($title); 
cRender::button("Back to Backends", "backends.php?$sAppQS");
cRender::button("Backend Tier Calls", "backcalls.php?$sBackendQS");
cRender::button("Backend Transaction Calls", "backtrans.php?$sBackendQS");
cRender::appdButton(cAppDynControllerUI::remoteServices($aid));

	//********************************************************************
	if (cAppdyn::is_demo()){
		cRender::errorbox("function not support ed for Demo");
		exit;
	}
	//********************************************************************

?>
<br>
<span id="progress">
<?php $aTransactions = cAppdyn::GET_BackendCallerTransactions($app, $backend);?>
</span>


<table class='maintable'>
	<tr><td><?php
		$sMetricUrl=cAppDynMetric::appCallsPerMin();
		cChart::add("Overall Calls per min ($app)", $sMetricUrl, $app);
	?></td></tr>
	<tr><td><?php
		$sMetricUrl=cAppDynMetric::backendCallsPerMin($backend);
		cChart::add("Overall Calls per min ($backend)", $sMetricUrl, $app);
	?></td></tr>
	<tr><td><?php
		$sMetricUrl=cAppDynMetric::backendResponseTimes($backend);
		cChart::add("response times ($backend)", $sMetricUrl, $app);
	?></td></tr>
</table>
<p>

<table class='maintable'>
	<?php
		foreach ($aTransactions as $oItem){
			$sMetric = $oItem->metric."|Average Response Time (ms)";

			echo "<tr><td class='".cRender::getRowClass()."'>";
				cChart::add($sMetric, $sMetric, $app);	
			echo "</td></tr>";
		}
	?>
</table>
<p>
<?php	
cChart::do_footer();

cRender::html_footer();
?>
