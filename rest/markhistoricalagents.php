<?php

/**************************************************************************
Copyright (C) Chicken Katsu 2013-2021 

This code is protected by copyright under the terms of the 
Creative Commons Attribution-NonCommercial-NoDerivatives 4.0 International License
http://creativecommons.org/licenses/by-nc-nd/4.0/legalcode

For licenses that allow for commercial use please contact cluck@chickenkatsu.co.uk

// USE AT YOUR OWN RISK - NO GUARANTEES OR ANY FORM ARE EITHER EXPRESSED OR IMPLIED
**************************************************************************/

$home="..";
require_once "$home/inc/common.php";


//###################### DATA #############################################
$sNodes = cHeader::GET(cRenderQS::NODE_IDS_QS);
if ($sNodes == null || $sNodes=="")
	cDebug::error("missing node IDs");
$aNodes= json_decode($sNodes);

if (count($aNodes) > 50){
	http_response_code(501);
	cDebug::error("too many nodes, must limit to 50");
	return;
}

cADController::Mark_historical_nodes($aNodes);

//*************************************************************************
//* output
//*************************************************************************
cCommon::write_json("ok");	
return;
?>
