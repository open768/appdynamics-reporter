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


//-----------------------------------------------
$app = cHeader::get(cRender::APP_QS);
$aid = cHeader::get(cRender::APP_ID_QS);
$sAppQS = cRender::get_base_app_QS();


//####################################################################
cRender::html_header("Web browser - Real user monitoring");
cRender::force_login();
cChart::do_header();

$title ="$app&gt;Web Real User Monitoring";
cRender::show_time_options( $title); 

cRenderMenus::show_apps_menu("Show Web RUM for:", "apprum.php");
cRender::appdButton(cAppDynControllerUI::webrum($aid));

//####################################################################
?><h2>Overall Statistics</h2><?php
$aMetrics = [];
$aMetrics[] = ["Overall Calls per min",cAppDynMetric::appCallsPerMin()];
$aMetrics[] = ["Overall response time in ms", cAppDynMetric::appResponseTimes()];
cRender::render_metrics_table($aid, $aMetrics,2,cRender::getRowClass());			

?><h2>Browser Stats for (<?=$app?>)</h2><?php
cRender::button("Show Page Statistics", "rumstats.php?$sAppQS");
$aMetrics = [];
$aMetrics[] = ["Page requests per minute",cAppDynMetric::webrumCallsPerMin()];
$aMetrics[] = ["Page response time",cAppDynMetric::webrumResponseTimes()];
$aMetrics[] = ["Page connection time",cAppDynMetric::webrumTCPTime()];
$aMetrics[] = ["Page Server time",cAppDynMetric::webrumServerTime()];
$aMetrics[] = ["Page first byte time",cAppDynMetric::webrumFirstByte()];
cRender::render_metrics_table($app, $aMetrics,2,cRender::getRowClass());			


cChart::do_footer();

cRender::html_footer();
?>
