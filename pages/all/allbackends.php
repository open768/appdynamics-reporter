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
require_once "$root/inc/charts.php";


//-----------------------------------------------

//####################################################################
cRenderHtml::header("All Remote Services");
cRender::force_login();
cChart::do_header();
$title ="All Remote Services";

//####################################################################
$oApps = cADController::GET_Applications();
cRender::button("Sort by Backend Name", "allbackendsbyname.php");
	//********************************************************************
	if (cAD::is_demo()){
		cRender::errorbox("function not support ed for Demo");
		cRenderHtml::footer();
		exit;
	}
	//********************************************************************

?>

<h2><?=$title?></h2>
<ul><?php
	foreach ($oApps as $oApp){
		?><li><a href="#<?=$oApp->id?>"><?=cRender::show_name(cRender::NAME_APP,$oApp)?></a><?php
	}
?></ul><?php

//####################################################################
foreach ($oApps as $oApp){
	$sApp = $oApp->name;
	$sID = $oApp->id;
	$sUrl = cHttp::build_url("../app/appext.php", cRender::APP_QS, $sApp);
	$sUrl = cHttp::build_url($sUrl, cRender::APP_ID_QS, $sID);
	
	?><hr><h2><a name="<?=$sID?>"><?=cRender::show_name(cRender::NAME_APP,$oApp)?></a></h2>
		<?php cRenderMenus::show_app_functions($oApp); ?>
		<?=cRender::button("See Details...", $sUrl);?>
		<?php
			$aBackends = $oApp->GET_Backends();
			$aMetrics = [];
			foreach ($aBackends as $oItem){
				$sMetric = cADMetric::backendResponseTimes($oItem->name);
				$aMetrics[] = [
					cChart::LABEL=>"Backend Response Times: $oItem->name", 
					cChart::METRIC=>$sMetric, 
					cChart::HIDEIFNODATA=>1
				];
			}
			cChart::render_metrics($oApp, $aMetrics, cChart::CHART_WIDTH_LETTERBOX/3);
			cDebug::flush();
		?>
	<?php
}
cChart::do_footer();
cRenderHtml::footer();
?>
