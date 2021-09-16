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
cRenderHtml::header("Agent License Usage");
cRender::force_login();

//####################################################################
cRender::show_top_banner("Agent licenses used"); 

//********************************************************************
if (cAD::is_demo()){
	cRender::errorbox("function not support ed for Demo");
	cRenderHtml::footer();
	exit;
}
//********************************************************************

try{
	$aRules=cAD_RestUI::GET_allocationRules();
}
catch (Exception $e){
	cRender::errorbox("unable to get license details - $e");
	cRenderHtml::footer();
	exit;	
}

function filter_sort($a,$b){
	$k1 = "$a->type.$a->operator.$a->entityName";
	$k2 = "$b->type.$b->operator.$b->entityName";
	return strnatcasecmp($k1,$k2);
}

cRender::appdButton(cADControllerUI::licenses());

$iRuleCount = 0;
cRenderCards::card_start("Summary");
	cRenderCards::body_start();
		echo "<ul>";
			foreach ($aRules as $oRule){
				echo "<li><a href='#$iRuleCount'>$oRule->allocationName</a>";
				$iRuleCount++;
			}
		echo "</ul>";
	cRenderCards::body_end();
cRenderCards::card_end();

$iRuleCount = 0;
foreach ($aRules as $oRule){
	cRenderCards::card_start("<a name='$iRuleCount'>rule: $oRule->allocationName</a>");
		cRenderCards::body_start();
			//cDebug::vardump($oRule);
			$sAllocID = $oRule->allocationId;
			echo "License Key:";
			cRenderCards::chip("$oRule->licenseKey");
			echo "<hr>";
			
			//----------------------------------------------------------------------
			$aPackages = $oRule->allocatedPackages;
			echo "Packages:<br>";
			foreach ($aPackages as $oPackage)
				cRenderCards::chip("$oPackage->packageName : $oPackage->allocatedUnits");
			echo "<hr>";
			
			//----------------------------------------------------------------------
			$aFilters = $oRule->filters;
			if (count($aFilters) ==0)
				echo "Applies to all agents";
			else{
				uasort($aFilters, "filter_sort");
				echo "Filters:<br>";
				foreach ($aFilters as $oFilter){
					$sEntity = 	$oFilter->entityName;
					$sOperator = $oFilter->operator;
					if ($sOperator === "ID_EQUALS") $sOperator = "=";
					cRenderCards::chip ("$oFilter->type <i>$sOperator</i> $sEntity");
				}
			}
			echo "<hr>";
			cDebug::flush();
			
			//----------------------------------------------------------------------
			echo "connected agents:<br>";
			$aAllocHosts = cAD_RestUI::GET_allocationHosts($sAllocID);
			$aHosts = [];
			foreach ($aAllocHosts as $oHost)
				$aHosts[] = $oHost->hostId;
				
			if (count($aHosts) == 0)
				echo "<b>No connected hosts found!</b>";
			else{
				$aUsage = 	cAD_RestUI::GET_license_usage($sAllocID, $aHosts);
				$aAnalysed = cADUtil::analyse_license_usage($aUsage);
				$aKeys = array_keys($aAnalysed);

				$iMax = 0;
				?><table class="maintable" border="1">
					<tr><?php
						foreach ($aKeys as $sKey){
							echo "<th width='33%'>";
								echo "$sKey ";
								cRenderCards::chip("".count($aAnalysed[$sKey])." agents");
							echo "</th>";
						}
					?></tr>
					<tr><?php
						foreach ($aKeys as $sKey){
							echo "<td valign='top'><font size='-1'>";
								foreach ($aAnalysed[$sKey] as $sHostID)
									echo "$sHostID, ";
							echo "</font></td>";
						}
					?></tr>
				</table><?php
			}
			
		cRenderCards::body_end();
	cRenderCards::card_end();	
	$iRuleCount ++;
}


cRenderHtml::footer();
?>
