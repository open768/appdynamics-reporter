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
//####################################################################
$home="../..";
require_once "$home/inc/common.php";
require_once "$root/inc/charts.php";


//display the results
$oTier = cRenderObjs::get_current_tier();
$oApp = $oTier->app;
$sTierQS = cRenderQS::get_base_tier_QS($oTier);

//####################################################################
cRenderHtml::header("Backends resolving to tier: $oTier->name");
cRender::force_login();


//********************************************************************
if (cAD::is_demo()){
	cCommon::errorbox("function not supported for Demo");
	cRenderHtml::footer();
	exit;
}
//********************************************************************

//####################################################################
$oCred = cRenderObjs::get_appd_credentials();
cDebug::flush();

//####################################################################
$aData = cAD_RestUI::GET_tier_backends($oTier);
if ($aData == null || count($aData) ==0)
	$iCount = 0;
else
	$iCount = count($aData);

cRenderCards::card_start();
	cRenderCards::body_start();
		if ($iCount ==0)
			echo("<font color='red'>no backends found</font>");
		else
			echo "there are $iCount backends that resolve to this tier";
	cRenderCards::body_end();
	cRenderCards::action_start();
		if ($oCred->restricted_login == null){
			cRenderMenus::show_tier_functions();
			cRenderMenus::show_tier_menu("Change Tier", "tierbackends.php");
			cDebug::flush();
		}
		cRenderCards::action_end();
cRenderCards::card_end();
	
if ($iCount>0 ){
	//cDebug::vardump($aData[0]);
	?>
		<table class="maintable" cellpadding="4" border=1>
			<tr class="tableheader">
				<th>Type</th>
				<th>name</th>
				<th>Action</th>
			</tr>
			<?php
				foreach ($aData as $oBackend){
					$sUrl = cHttp::build_url("../backend/delbackend.php", cRender::BACKEND_QS, $oBackend->id);
					?><tr>
						<td><?=$oBackend->resolutionInfo->exitPointType?></td>
						<td><?=$oBackend->displayName?></td>
						<td><?php
							cRender::button(
								'<span class="material-icons-outlined">delete</span>', 
								$sUrl, true,null,"delback"
							);
						?></td>
					</tr><?php
				}
			?>
		</table>
	<?php
}	

//####################################################################
cRenderHtml::footer();
?>
