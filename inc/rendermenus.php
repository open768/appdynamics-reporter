<?php

/**************************************************************************
Copyright (C) Chicken Katsu 2013-2021 

This code is protected by copyright under the terms of the 
Creative Commons Attribution-NonCommercial-NoDerivatives 4.0 International License
http://creativecommons.org/licenses/by-nc-nd/4.0/legalcode

For licenses that allow for commercial use please contact cluck@chickenkatsu.co.uk

// USE AT YOUR OWN RISK - NO GUARANTEES OR ANY FORM ARE EITHER EXPRESSED OR IMPLIED
**************************************************************************/


//#######################################################################
//#######################################################################
class cRenderMenus{
	
	//******************************************************************************************
	// menus are populated in the footer() function in renderhtml.php
	//******************************************************************************************
	public static function show_app_functions($poApp=null){
		global $home;
		cDebug::enter();
		$oCred = cRenderObjs::get_AD_credentials();
		if ($oCred->restricted_login) {
			cRender::button($poApp->name,null);
			cDebug::leave();
			return;
		}
		
		if ($poApp == null) $poApp = cRenderObjs::get_current_app();
		?>
			<div
				type="admenus" menu="appfunctions" 
				home="<?=$home?>"
				appname="<?=$poApp->name?>" appid="<?=$poApp->id?>">
				<font class="ui-selectmenu-text"><?=$poApp->name?></font>
			</div>
		<?php
		cDebug::leave();
	}

	//******************************************************************************************
	public static function show_apps_menu($psCaption, $psURLFragment, $psExtraQS=""){
		global $home;
	
		cDebug::enter();
		$oCred = cRenderObjs::get_AD_credentials();
		if ($oCred->restricted_login) {
			cRender::button($psCaption,null);
			cDebug::leave();
			return;
		}
		
		$sApps_fragment = self::get_apps_fragment();

		//TODO change to a DIV - widget can replace with a select menu
		?>
			<SELECT
				type="admenus" menu="appsmenu" 
				home="<?=$home?>"
				caption="<?=$psCaption?>" url="<?=$psURLFragment?>" 
				extra="<?=$psExtraQS?>" <?=$sApps_fragment?>>
				<option selected><?=$psCaption?> - please wait
			</SELECT>
		<?php
		self::show_app_functions();
		cDebug::leave();
	}
	
	//******************************************************************************************
	public static function show_app_agent_menu($poApp = null){
		global $home;

		cDebug::enter();
		$oCred = cRenderObjs::get_AD_credentials();
		if ($oCred->restricted_login) {
			cDebug::leave();
			return;
		}
		
		if ($poApp == null) $poApp = cRenderObjs::get_current_app();
		//TODO change to a DIV - widget can replace with a select menu
		?>
			<SELECT 
				type="admenus" menu="appagents" 				
				home="<?=$home?>"
				appname="<?=$poApp->name?>" appid="<?=$poApp->id?>">
			</SELECT>
		<?php
		cDebug::leave();
	}

	//******************************************************************************************
	//******************************************************************************************
	public static function get_apps_attr(){

		cDebug::enter();
		try{
			$aApps = cADController::GET_all_Applications();
		}
		catch (Exception $e)
		{
			return null;
		}
		uasort($aApps,"sort_by_app_name" );
		$iCount=0;
		$sfragment = "";
		foreach ($aApps as $oApp){
			$iCount++;
			if ($iCount > 1)$sfragment.=",";
			$sfragment.= "'appname.$iCount':\"".$oApp->name."\",'appid.$iCount':\"$oApp->id\"";
		}
		
		cDebug::leave();
		return $sfragment;
	}
	//******************************************************************************************
	public static function get_apps_fragment(){

		cDebug::enter();
		try{
			$aApps = cADController::GET_all_Applications();
		}
		catch (Exception $e)
		{
			cCommon::errorbox("Oops unable to get application data from controller");
			cDebug::leave();
			exit;
		}
		uasort($aApps,"sort_by_app_name" );
		$iCount=0;
		$sApps_fragment = "";
		foreach ($aApps as $oApp){
			$iCount++;
			$sApps_fragment.= "appname.$iCount =\"".$oApp->name."\" appid.$iCount=\"$oApp->id\" ";
		}
		
		cDebug::leave();
		return $sApps_fragment;
	}

	//******************************************************************************************
	//******************************************************************************************
	// this is a slightly different type of menu - so initialises differently
	public static function top_menu(){
		global $home;

		cDebug::enter();
		
		$oCred = cRenderObjs::get_AD_credentials();
		if ($oCred == null) return;

		$sApps_attr = self::get_apps_attr();
		if ($sApps_attr== null) return;

		?>
			<script>
				function init_top_menu(){
					$oDiv = $("#<?=cRenderHtml::NAVIGATION_ID?>");
					$oDiv.attr({
						home:"<?=$home?>",
						controller:"<?=$oCred->host?>",
						restricted:"<?=$oCred->restricted_login?>",
						<?=$sApps_attr?>
					})
										
					//now trigger the rendering of the top menu
					cTopMenu.render( $oDiv);
				}
				
				$(init_top_menu);
			</script>
		<?php
		cDebug::leave();
	}
	
	//******************************************************************************************
	//******************************************************************************************
	public static function show_tier_functions($poTier = null, $psNode=null){
		global $home;

		cDebug::enter();
		$oCred = cRenderObjs::get_AD_credentials();
	
		if ($oCred->restricted_login) {
			cRender::button($poTier->name,null);
			cDebug::leave();
			return;
		}
		if ($poTier == null){
			$poTier = cRenderObjs::get_current_tier();
		}
		//TODO change to a DIV - widget can replace with a select menu
		?>
			<SELECT 
				type="admenus" menu="tierfunctions"  
				home="<?=$home?>"
				tier="<?=$poTier->name?>" tid="<?=$poTier->id?>" node="<?=$psNode?>">
				<option selected><?=$poTier->name?> - please wait
			</SELECT>
		<?php
		cDebug::leave();
	}
	
	//******************************************************************************************
	public static function show_tier_menu($psCaption, $psURLFragment, $psExtraQS=""){
		global $home;
		
		cDebug::enter();
		$oCred = cRenderObjs::get_AD_credentials();
		if ($oCred->restricted_login){
			cDebug::leave();
			return;
		}

		$oApp = cRenderObjs::get_current_app();
		
		try{
			$oTiers = $oApp->GET_Tiers();
		}
		catch (Exception $e)
		{
			cCommon::errorbox("Oops unable to get tier data from controller");
			cDebug::leave();
			exit;
		}
		
		$sFragment = "";
		$iCount = 1;
		foreach ($oTiers as $oTier){
			$sFragment .= " tname.$iCount=\"".$oTier->name."\" tid.$iCount=\"$oTier->id\" ";
			$iCount++;
		}
		
		//TODO change to a DIV - widget can replace with a select menu
		?>
			<SELECT 
				type="admenus" menu="tierchangemenu" 
				home="<?=$home?>"
				caption="<?=$psCaption?>" url="<?=$psURLFragment?>" 
				extra="<?=$psExtraQS?>" <?=$sFragment?>>
				<option selected><?=$psCaption?> - please wait
			</SELECT>
		<?php
		cDebug::leave();
	}	
}
?>
