<?php

/**************************************************************************
Copyright (C) Chicken Katsu 2013-2016 

This code is protected by copyright under the terms of the 
Creative Commons Attribution-NonCommercial-NoDerivatives 4.0 International License
http://creativecommons.org/licenses/by-nc-nd/4.0/legalcode

For licenses that allow for commercial use please contact cluck@chickenkatsu.co.uk

// USE AT YOUR OWN RISK - NO GUARANTEES OR ANY FORM ARE EITHER EXPRESSED OR IMPLIED
**************************************************************************/

require_once("$phpinc/appdynamics/appdynamics.php");
require_once("$phpinc/appdynamics/common.php");
require_once("$phpinc/appdynamics/metrics.php");
require_once("$phpinc/appdynamics/account.php");
require_once("$phpinc/ckinc/debug.php");
require_once("$phpinc/ckinc/session.php");
require_once("$phpinc/ckinc/common.php");
require_once("$phpinc/ckinc/hash.php");

//######################################################################
class cMetricItem{
	public $value;
	public $max;
	public $date;
}

//######################################################################
class cMetricOutput{
	public $div;
	public $metric;
	public $app;
	public $data = [];
	
	public function add($psDate, $piValue, $piMax = null){
		$oItem = new cMetricItem;
		$oItem->value = $piValue;
		$oItem->max = $piMax;
		$oItem->date = $psDate;
		
		$this->data[] = $oItem;
	}
}

//######################################################################
class cMergedMetrics{
	
	public $sourceData = [];
	public $data = [];
	public $dates = [];
	
	public function add( $poMetricOutput){
		if (count($poMetricOutput->data) >0){
			$this->sourceData[] = $poMetricOutput;
			$aAssocList = [];
			foreach ($poMetricOutput->data as $oItem){
				$this->dates[$oItem->date]=1;
				$aAssocList[$oItem->date] = $oItem;
			}
			$this->data[] = $aAssocList;
		}
	}
	//**********************************************************************
	private function pr__get_filename(){
		$sAggregated = "";
		
		foreach ($this->sourceData as $oMetricOutput)
			$sAggregated .= $oMetricOutput->app.$oMetricOutput->metric;
		$sHash = cHash::hash($sAggregated);
		cDebug::write($sHash);
		return $sHash;
	}
	
	//**********************************************************************
	public function write_csv(){
		//--write CSV header to file
		cCommon::echo("Merged_metrics:, ".date(cCommon::EXCEL_DATE_FORMAT,time()));
		cCommon::echo("");
		
		$sAppLine = "application";
		$sMetricLine = "metric";
		$sColumnLine = "";
		foreach ($this->sourceData as $oMetricOutput){
			$sAppLine .= ",$oMetricOutput->app,";
			$sMetricLine .= ",$oMetricOutput->metric,";
			$sColumnLine .= ",value,max";
		}
		cCommon::echo($sAppLine);
		cCommon::echo($sMetricLine);
		cCommon::echo($sColumnLine);
		
		//--sort the dates
		$aKeys = array_keys($this->dates);
		uasort($aKeys, "pr__sort_dates");
		
		//--output data 
		foreach($aKeys as $sDate){
			$oDate = DateTime::createFromFormat(DateTime::W3C, $sDate);
			$sXLDate = $oDate->format(cCommon::EXCEL_DATE_FORMAT);

			$sLine = $sXLDate;
			foreach ($this->data as $aMetrics){
				if (array_key_exists($sDate,$aMetrics)){
					$oItem = $aMetrics[$sDate];
					$sLine.=",$oItem->value,$oItem->max";
				}else
					$sLine.=",,";
			}
			cCommon::echo($sLine);
		}
	}
}

function pr__sort_dates($a,$b){
	return strtotime($a) - strtotime($b);
}

//######################################################################
class cMetric{
	public static function get_metric($psApp, $psMetric, $pbPreviousPeriod = false){
		$oOutput = new cMetricOutput;
		$oOutput->metric = $psMetric;
		$oOutput->app = $psApp;
		
		if (strstr($psMetric, cAppDynMetric::USAGE_METRIC)){
			//license usage metrics are special
			$aParams = explode("/",$psMetric);
			try{
				$aData = cAppDynAccount::GET_license_usage($aParams[1], $aParams[2]);
			}
			catch (Exception $e){}
			
			if ($aData)
				foreach ($aData as $oItem){
					$oDate = date_create_from_format(cAppdynCore::DATE_FORMAT,$oItem->date);
					$sDate = $oDate->format(DateTime::W3C);
					$oOutput->add($sDate,$oItem->value);
				}
		}else{
			//normal metrics
			$oTime= cRender::get_times();
			$epochTo = $oTime->end;
			$epochFrom = $oTime->start;
			
			
			if ($pbPreviousPeriod){
				$iDiff = $epochTo - $epochFrom;
				$epochTo = $epochFrom;
				$epochFrom = $epochTo - $iDiff;
			}
			
			try{
				$aData = cAppDynCore::GET_MetricData_between($psApp, $psMetric, $epochFrom, $epochTo);
			}
			catch (Exception $e){}
			if ($aData){
				//add a null if the first  item didnt have the expected timstamp
				if (count($aData) > 0){
					$oFirst = $aData[0];
					if ($oFirst->startTimeInMillis > $epochFrom){
						cDebug::write("first item didnt start on $epochFrom");
						$sDate = date(DateTime::W3C, $epochFrom/1000); 
						$oOutput->add($sDate,null,0 );
						
						$sDate = date(DateTime::W3C, ($oFirst->startTimeInMillis-1)/1000 ); 
						$oOutput->add($sDate,null,0 );
					}
				}
				
				//add the other data
				foreach ($aData as $oRow){
					$sDate = date(DateTime::W3C, $oRow->startTimeInMillis/1000); 
					$iMaxval = max($oRow->max, $oRow->value);
					
					$oOutput->add($sDate,$oRow->value,$iMaxval );
				}
				
				//add a null if the last item didnt have the expected timstamp
				if ($oRow->startTimeInMillis < $epochTo){
					cDebug::write("last item didnt end on $epochTo");
					$sDate = date(DateTime::W3C, ($oRow->startTimeInMillis+1)/1000); 
					$oOutput->add($sDate,null,0 );
					
					$sDate = date(DateTime::W3C, $epochTo/1000); 
					$oOutput->add($sDate,null,0 );
					
				}
			}
		}
		
		return $oOutput;
	}
}