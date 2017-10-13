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
cRender::html_header("Backend Calls");
cRender::force_login();
?>
	<script type="text/javascript" src="js/remote.js"></script>
	
<?php
cChart::do_header();


//get passed in values
$app = cHeader::get(cRender::APP_QS);
$gsBackend = cHeader::get(cRender::BACKEND_QS);
$sAppQs = cRender::get_base_app_QS();
$oBackends =cAppdyn::GET_Backends($app);
$gsBaseUrl = cHttp::build_url("backcalls.php",$sAppQs);

$title= "$app&gt;Remote Service&gt;$gsBackend";
cRender::show_time_options($title); 
cRenderMenus::show_app_functions();
?>
<select id="menuBackends">
	<option selected disabled>Remote Services</option>
	<?php
		foreach ($oBackends as $oBackend){
			$sBackend = $oBackend->name;
			$sDisabled = ($sBackend == $gsBackend?"disabled":"");
			?><option <?=$sDisabled?> value="<?=cHttp::build_url($gsBaseUrl, cRender::BACKEND_QS, $sBackend)?>"><?=$sBackend?></option><?php
		}
	?>
</select>
	<script language="javascript">
		$(  function(){$("#menuBackends").selectmenu({change:common_onListChange});  }  );
	</script>
<?php
cRender::button("transactions for this remote service", cHttp::build_url("backtrans.php?$sAppQs",cRender::BACKEND_QS,$gsBackend));
?>
<br>
<?php
//****************************************************************************
// work through each tier
$sClass = cRender::getRowClass();
cChart::$width = cRender::CHART_WIDTH_LETTERBOX/2;
?>
<h2>(<?=$gsBackend?>) Remote Service</h2>
<h3>Overall Application Statistics</h3>
<table class='maintable'>
	<tr class="<?=$sClass?>">
		<td><?php
			$sMetricUrl=cAppDynMetric::appCallsPerMin();
			cChart::add("Overall Calls per min ($app)", $sMetricUrl, $app, cRender::CHART_HEIGHT_LETTERBOX2);
		?></td>
		<td><?php
			$sMetricUrl=cAppDynMetric::appResponseTimes();
			cChart::add("Overall Response Times in ms ($app)", $sMetricUrl, cRender::CHART_HEIGHT_LETTERBOX2);
		?></td>
	</tr>
</table>
<p>
<?php
//****************************************************************************
$sClass = cRender::getRowClass();
?>
<h3><?=$gsBackend?> Statistics</h3>
<table class='maintable'>
	<tr class="<?=$sClass?>">
	<td><?php
		$sMetricUrl=cAppDynMetric::backendCallsPerMin($gsBackend);
		cChart::add("Calls per min for ($gsBackend)", $sMetricUrl, $app, cRender::CHART_HEIGHT_LETTERBOX2);
	?></td>
	<td><?php
		$sMetricUrl=cAppDynMetric::backendResponseTimes($gsBackend);
		cChart::add("Response Times in ms for ($gsBackend)", $sMetricUrl, $app, cRender::CHART_HEIGHT_LETTERBOX2);
	?></td>
	</tr>

</table>
<p>

<div id="divWorking">
<?php
//****************************************************************************
$oResponse =cAppdyn::GET_BackendCallerTiers($app,$gsBackend);
?>
</div>
<script language="javascript">
	$(function(){$("#divWorking").hide();})
</script>

<h3>Tiers Calling <?=$gsBackend?></h3>
<table class='maintable'>
	<?php
	foreach ( $oResponse as $oItem){
		
		$tier = $oItem->tier;
		$metric = $oItem->name;
		$sClass = cRender::getRowClass();

		?><tr class="<?=$sClass?>">
			<td ><?php
				$sMetricUrl = cAppDynMetric::tierExtCallsPerMin($tier, $metric);
				cChart::add("Calls per min from ($tier) to ($gsBackend)", $sMetricUrl, $app, cRender::CHART_HEIGHT_LETTERBOX2);	
			?></td>
			<td><?php
				$sMetricUrl = cAppDynMetric::tierExtResponseTimes($tier, $metric);
				cChart::add("Response Times in ms from ($tier) to ($gsBackend)", $sMetricUrl, $app, cRender::CHART_HEIGHT_LETTERBOX2);	
			?></td>
		</tr><?php
	}
	?>
</table>
<?php
cChart::do_footer();

cRender::html_footer();
?>
