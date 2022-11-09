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



//####################################################################
$sMetric = cHeader::get(cRenderQS::METRIC_QS);
$oApp = cRenderObjs::get_current_app();
if (!$oApp->name) $oApp->name = "No Application Specified";
$sTitle = cHeader::get(cRenderQS::TITLE_QS);

//####################################################################
cRenderHtml::$load_google_charts = true;
cRenderHtml::header($sTitle);
cRender::force_login();

//####################################################################
cChart::do_header();
cChart::$width=cChart::CHART_WIDTH_LARGE;
cChart::$show_zoom = false;

//####################################################################
$oItem = new cChartMetricItem();
$oItem->metric = $sMetric;
$oItem->caption = $sTitle;

$oChart = new cChartItem();
$oChart->metrics[] = $oItem;
$oChart->title = $sTitle;
$oChart->app = $oApp;
$oChart->height = 700;

?>
	<table class="maintable"><tr><td>
	<?php
		$oChart->write_html();
	?>
	</td></tr></table><?php

cChart::do_footer(false);
cRenderHtml::footer();
?>
