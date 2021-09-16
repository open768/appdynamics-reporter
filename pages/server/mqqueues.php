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


$sNode = cHeader::get(cRender::NODE_QS);
if (!$sNode){
	cRender::errorbox("no Node specified");
	exit;
}
$sQManager = cHeader::get(cRender::SERVER_MQ_MANAGER_QS);
if (!$sQManager){
	cRender::errorbox("no Queue Manager specified");
	exit;
}

cRender::force_login(); 

//####################################################################
cRenderHtml::header("MQ Queues for $sQManager on node $sNode");
cChart::do_header();
cChart::$hideGroupIfNoData = true;

//********************************************************************
if (cAD::is_demo()){
	cRender::errorbox("function not supported for Demo");
	cRenderHtml::footer();
	exit;
}


//####################################################################

//####################################################################
cRenderCards::card_start();
	cRenderCards::action_start();
		cRender::button("Back to MQ nodes", "mq.php");	
		cRender::button("Back to $sNode", cHttp::build_url("mqnode.php",cRender::NODE_QS, $sNode));	
		$sUrl = cHttp::build_url("mqqueues.php", cRender::NODE_QS, $sNode);
		$sUrl = cHttp::build_qs($sUrl, cRender::SERVER_MQ_MANAGER_QS, $sQManager);
		if (cRender::is_list_mode())
			cRender::button("show as buttons", $sUrl);
		else
			cRender::button("show as list", $sUrl."&".cRender::LIST_MODE_QS);
	cRenderCards::action_end();
cRenderCards::card_end();

// get the list of all queues for this manager
$sMetricPath= cADMetric::serverMQQueues($sNode, $sQManager);  
$aData = (cADApp::$server_app)->GET_Metric_heirarchy($sMetricPath, true);
$iCount = count($aData);
cRenderCards::card_start();
	cRenderCards::body_start();
		if ($iCount == 0)
			cRender::errorbox("sorry - no Queues found");
		else{
			uasort($aData,"sort_by_app_name" );
			
			if (cRender::is_list_mode()){
				echo "<b>MQ Queues for $sQManager on node $sNode</b><p>&nbsp;<p>";
				foreach ($aData as $oItem)
					echo "$oItem->name<br>";	
			}else{
				if ($iCount > cRender::$MAX_ITEMS_PER_PAGE){
					cRenderCards::chip("thats a lot of charts");
					echo "<br>";
				}
				$aMetrics = [];
				foreach ($aData as $oItem){
					if ($oItem->type === "folder")
						if (cRender::is_list_mode())
							echo "$oItem->name<br>";	
						else{
							$sMetric = cADMetric::serverMQQueueCurrent($sNode, $sQManager, $oItem->name);
							$sTitle = $oItem->name." : Current";
							$aMetrics[] = [cChart::LABEL=>$sTitle, cChart::METRIC=>$sMetric];
						}
				}
				$srv_app = new cADApp(cADCore::SERVER_APPLICATION,cADCore::SERVER_APPLICATION);
				cChart::metrics_table($srv_app, $aMetrics, 6, cRender::getRowClass(),cChart::CHART_HEIGHT_SMALL,cChart::CHART_WIDTH_LARGER/6);
			}
		}
	cRenderCards::body_end();
cRenderCards::card_end();

//####################################################################
cChart::do_footer();
cRenderHtml::footer();
?>
