<?php

/**************************************************************************
Copyright (C) Chicken Katsu 2013-2018 

This code is protected by copyright under the terms of the 
Creative Commons Attribution-NonCommercial-NoDerivatives 4.0 International License
http://creativecommons.org/licenses/by-nc-nd/4.0/legalcode

For licenses that allow for commercial use please contact cluck@chickenkatsu.co.uk

// USE AT YOUR OWN RISK - NO GUARANTEES OR ANY FORM ARE EITHER EXPRESSED OR IMPLIED
**************************************************************************/

//####################################################################
$home="../..";
require_once "$home/inc/common.php";


//####################################################################
cRenderHtml::header("test the API");
cRender::force_login();

//####################################################################
cRender::show_top_banner("test the API"); 

//####################################################################
	?>
		<FORM method="get" action='<?="$home/rest/getMetric.php"?>'>
			Application name:<br>
			<input type="text" name="<?=cRender::APP_QS?>"><p>
			
			enter the metric below<br>
			<textarea name="<?=cRender::METRIC_QS?>" rows="5" cols="80" wrap="soft"></textarea>
			<br>
			<input type="submit">
		</form>
	<?php

//####################################################################
cRenderHtml::footer();
?>
