//###############################################################################
var cChartConsts={
	METRIC_FIELD: "cmf.",
	COUNT_FIELD: "ccf",
	TITLE_FIELD: "ctf.",
	APP_FIELD: "caf.",
	CHART_ALL_CSV:"/pages/all_csv.php"
}

var cCharts={
	queue: new cHttpQueue,
	show_export_all : true,
	home:"",
	
	
	//*********************************************************
	rememberChart: function(psApp, psMetric, psTitle){
		var oItem = new cChartItem(psApp, psMetric, psTitle);
		cCharts.allCharts.push(oItem);
	},
	
	//*********************************************************
	loadCharts: function(){	
		var iCount ,oInput;
		var oThis = this;
		
		iCount = 0;
		var oForm = $("<form>", {id:"AllMetricsForm",method:"POST",action:this.home+cChartConsts.CHART_ALL_CSV,target:"_blank"});
		
		$("DIV[type='adchart']").each( //all  elements which have their type set to adchart
			function(pIndex, pElement){
				var oElement = $(pElement);
				
				var sAppName = oElement.attr("appName");
				var sMetric = oElement.attr("metric0");
				var sTitle = oElement.attr("title0");
				var sHome = oElement.attr("home");
				var iPrevious = 0;
				try {
					var sPrevious = oElement.attr("previous");
					iPrevious = parseInt(sPrevious);
				} catch (e){}
								
				oElement.adchart({
					appName:sAppName,
					home:sHome,
					title:sTitle,
					metric:sMetric,
					goUrl:oElement.attr("goUrl"),
					goCaption:oElement.attr("goLabel"),
					previous_period:iPrevious,
					width:oElement.attr("width"),
					height:oElement.attr("height"),
					showZoom:oElement.attr("showZoom"),
					showCompare:oElement.attr("showCompare"),
					hideIfNoData:oElement.attr("hideIfNoData"),
					hideGroupIfNoData:oElement.attr("hideGroupIfNoData")
				});
					
				//-------------build the form
				if(oThis.show_export_all){
					iCount++;
					oInput = $("<input>",{type:"hidden",name:cChartConsts.METRIC_FIELD+iCount,value:sMetric}	);
					oForm.append(oInput);
					oInput = $("<input>",{type:"hidden",name:cChartConsts.TITLE_FIELD+iCount,value:sTitle}	);
					oForm.append(oInput);
					oInput = $("<input>",{type:"hidden",name:cChartConsts.APP_FIELD+iCount,value:sAppName}	);
					oForm.append(oInput);
				}
			}
		);
		
		//complete the form
		if (iCount >0){
			oInput = $("<input>",{type:"hidden",name:cChartConsts.COUNT_FIELD,value:iCount}	);
			oForm.append(oInput);
			oInput = $("<input>",{type:"submit",name:"submit",value:"Export All as CSV", class:"mdl-button mdl-js-button mdl-button--raised mdl-js-ripple-effect"}	);
			oForm.append(oInput);
			$("#AllMetrics").empty().append(oForm);
		}
	},
	
	init:function(psHome){
		var oThis = this;
		this.home=psHome;
		//load google charts
		try{
			google.charts.load('current', {'packages':['corechart']});
		}
		catch (e){}
		google.charts.setOnLoadCallback( function(){oThis.loadCharts()})
	},
}
cCharts.queue.maxTransfers = 3	; 	//dont overload the controller


//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
$.widget( "ck.adchart",{
	//#################################################################
	//# Definition
	//#################################################################
	options:{
		title:null,
		home:"",
		appName:null,
		metric: null,
		width:null,
		height:null,
		onClick:null,
		shortNoData:false,
		showZoom:true,
		onSelect: null,
		previous_period:false,
		goUrl:null,
		goCaption:"Go",
		hideIfNoData:false,
		pr__upper_div: null,
		pr__lower_div: null
	},
	
	consts:{
		APP_QS:"app",
		METRIC_QS:"met",
		TITLE_QS:"tit",
		DIV_QS:"div",
		PREVIOUS_QS: "prv",
		METRIC_API:"rest/getMetric.php",
		CHART_PHP_FOLDER:"pages/charts",
		NO_DATA_HEIGHT: 90,
		SHORT_NO_DATA_HEIGHT: 40,
		csv_url:"allcsv.php",
		zoom_url:"metriczoom.php",
		compare_url:"compare.php",
		
		INFO_WIDTH:70,
		BUTTON_WIDTH:30
	},

	//#################################################################
	//# Constructor
	//#################################################################`
	_create: function(){
		var oThis, oElement;
		
		//set basic stuff
		oThis = this;
		oElement = oThis.element;
		oElement.uniqueId();
		
		//check for necessary classes
		if (!cQueueifVisible)			$.error("Queue on visible class is missing! check includes");	
		if (!bean)						$.error("bean class is missing! check includes");	
		if (!cHttp2)					$.error("http2 class is missing! check includes");	
		if (!oElement.gSpinner) 		$.error("gSpinner is missing! check includes");		
		if (!oElement.slideout) 		$.error("slideout is missing! check includes");		
		if (!$.event.special.inview)	$.error("inview class is missing! check includes");	
		if (!oElement.inViewport ) 		$.error("inViewport class is missing! check includes");	
		
		//check for required options
		var oOptions = this.options;
		if (!oOptions.title)			$.error("title  missing!");			
		if (!oOptions.metric)			$.error("metric  missing!");		
		if (!oOptions.width)			$.error("width missing!");		
		if (!oOptions.height)			$.error("height missing!");		
					
		//set display style
		oElement.removeClass();
		oElement.addClass("chart_widget");
	
		//set the DIV size
		oElement.outerWidth(oOptions.width );
		oElement.outerHeight(oOptions.height );
		oElement.css("max-width",""+oOptions.width+"px");
		
		//create overlapping divs
		this.pr__create_overlapping_divs();
		
		
		//wait for widget to become visible
		var oQueue = new cQueueifVisible();
		bean.on(oQueue, "status", 	function(psStatus){oThis.onStatus(psStatus);}	);				
		bean.on(oQueue, "start", 	function(){oThis.onStart();}	);				
		bean.on(oQueue, "result", 	function(poHttp){oThis.onResponse(poHttp);}	);				
		bean.on(oQueue, "error", 	function(poHttp){oThis.onError(poHttp);}	);				
		oQueue.go(oElement, this.pr__get_chart_url());
	},

	//*******************************************************************
	pr__create_overlapping_divs: function(){
		var oThis, oElement;
		var oOptions = this.options;
		oThis = this;
		oElement = oThis.element;
		
		oOptions.pr__upper_div = oElement.attr("id") + "_U";
		var oUpperDiv = $("<DIV>", {id:oOptions.pr__upper_div}).append("Please wait...");
		
		oOptions.pr__lower_div = oElement.attr("id") + "_L";
		var oLowerDiv = $("<DIV>", {id:oOptions.pr__lower_div}).append("Please wait...");

		oElement.slideout({width:oOptions.width, height:oOptions.height, uppercontent:oUpperDiv, lowercontent:oLowerDiv});
	},


	//#################################################################
	//# events
	//#################################################################`
	onStatus: function(psStatus){
		var oThis = this;
		var oOptions = this.options;
		var oElement = $("#"+oOptions.pr__upper_div );

		oElement.empty();
		oElement.addClass("chart_widget");
		oElement.addClass("chart_initialising");
		oElement.append(psStatus);
	},
	
	
	//*******************************************************************
	onError: function(poHttp, psMessage){
		var oThis = this;
		var oOptions = this.options;
		var oElement = $("#"+oOptions.pr__upper_div );
		var oConsts = this.consts;
				
		oElement.empty();
		oElement.addClass("ui-state-error");
		oElement.append("There was an error getting chart data for "+ oOptions.title);
		var btnForce = $("<button>").append("load");
		oElement.append(btnForce);
		btnForce.click( 		function(){oThis.onInView(true);}		);
		oElement.height(oConsts.SHORT_NO_DATA_HEIGHT);
	},

	//*******************************************************************
	onStart: function(poItem){
		var oOptions = this.options;
		var oElement = $("#"+oOptions.pr__upper_div );
		
		oElement.empty();
		oElement.removeClass();
		oElement.addClass("chart_widget");
		oElement.addClass("chart_loading");
		
		var oLoader = $("<DIV>");
		oLoader.gSpinner({scale: .25});
		oElement.append(oLoader).append("Loading: " + oOptions.title);
	},
	
	//*******************************************************************
	onResponse: function(poHttp){
		var oThis = this;
		var oOptions = this.options;
		var oElement = $("#"+oOptions.pr__upper_div );

		oElement.empty();
		oElement.removeClass();
		oElement.addClass("chart_widget");
		oElement.addClass("chart_drawing");
		
		var oResponse = poHttp.response;
		if (oResponse.data.length == 0){
			this.pr__no_data_found();
			return;
		}
		
		oElement.empty();
		oElement.append("Drawing Chart...");
		
		this.pr__draw_chart(poHttp.response);
	},

	//*******************************************************************
	onClickCSV: function(){
		var oOptions = this.options;
		var oConsts = this.consts;
		
		var oParams={};
		oParams[oConsts.METRIC_QS]=oOptions.metric;
		oParams[oConsts.APP_QS]=oOptions.appName;
		oParams[oConsts.TITLE_QS]=oOptions.title;
		oParams["csv"]=1;
		var sUrl = cBrowser.buildUrl(this.options.home+"/pages/"+oConsts.csv_url, oParams);
		window.open(sUrl);
	},
	
	//*******************************************************************
	onClickGo: function(){
		var oOptions = this.options;
		document.location.href = oOptions.goUrl;
	},
	
	//*******************************************************************
	onClickCompare: function(){
		var oOptions = this.options;
		var oConsts = this.consts;
		
		var oParams={};
		oParams[oConsts.METRIC_QS]=oOptions.metric;
		if (oOptions.appName)
			oParams[oConsts.APP_QS]=oOptions.appName;
		oParams[oConsts.TITLE_QS]=oOptions.title;
		var sUrl = cBrowser.buildUrl(oOptions.home+"/"+oConsts.CHART_PHP_FOLDER+"/"+oConsts.compare_url, oParams);
		window.open(sUrl);		
	},
	
	//*******************************************************************
	onClickZoom: function(){
		var oOptions = this.options;
		var oConsts = this.consts;
		
		var oParams={};
		oParams[oConsts.METRIC_QS]=oOptions.metric;
		oParams[oConsts.APP_QS]=oOptions.appName;
		oParams[oConsts.TITLE_QS]=oOptions.title;
		var sUrl = cBrowser.buildUrl(oOptions.home+"/"+oConsts.CHART_PHP_FOLDER+"/"+oConsts.zoom_url, oParams);
		window.open(sUrl);		
	},
	
	//#################################################################
	//# functions
	//#################################################################`
	pr__no_data_found: function(){
		var oThis = this;
		var oOptions = this.options;
		var oElement = this.element;
		var oConsts = this.consts;
		
		oElement.empty();
		oElement.removeClass();
		if (oOptions.hideIfNoData){
			oElement.addclass("charthidden");
			oElement.hide();	
		}else {
			oElement.addClass("chartnodata");
			oElement.append("No data found for "+ oOptions.title);
			oElement.height(oConsts.SHORT_NO_DATA_HEIGHT);
			if (oOptions.hideGroupIfNoData){
				//TODO hide parent if number of charts with data is 0
			}
		}
},

	//*******************************************************************
	pr__get_chart_url: function (){
		var sUrl;
		var oOptions = this.options;
		var oConsts = this.consts;
		
		var oParams = {};
		oParams[ oConsts.METRIC_QS ] = oOptions.metric;
		if (oOptions.appName) oParams[ oConsts.APP_QS ] = oOptions.appName;
		oParams[ oConsts.DIV_QS ] = "";
		if (oOptions.previous_period == 1) {
			oParams[ oConsts.PREVIOUS_QS ] = 1;
			oOptions.title = "(Previous) " + oOptions.title;
		}
		
		sUrl = cBrowser.buildUrl(this.options.home+"/"+this.consts.METRIC_API, oParams);
		return sUrl;
	},
	
	//*******************************************************************
	pr__draw_chart: function(poJson){
		var oThis = this;
		var oOptions = this.options;
		var oElement = $("#"+oOptions.pr__upper_div );
		var oConsts = this.consts;

		var oChart, aValues, dDate, iValue, iItemMax, iMax, iMaxObserved, iAvgObs, iSum, sMax, iMin;
		

		iMax = 0;
		iMaxObserved = 0;
		iSum = 0;
		
		//create the google data object
		var oData = new google.visualization.DataTable();
		oData.addColumn('datetime', 'Time');
		oData.addColumn('number', 'Value');		
		oData.addColumn('number', 'Max');		
		oData.addColumn({type: 'string', role: 'tooltip', p: {html: true}});		
		

		//build up the data array
		for (i=0; i<poJson.data.length; i++ ){
			var oItem = poJson.data[i];
			dDate = new Date(oItem.date);
			iValue = oItem.value;
			iItemMax = oItem.max;
			if (iItemMax == iValue) iItemMax = null;
			
			sMax= (iMax?"<br>Max: "+iItemMax:"");
			iMax = Math.max(iMax,iItemMax);
			iMaxObserved = Math.max(iMaxObserved,iValue);
			iSum += iValue;
			
			if (i==0)
				iMin = iValue;
			else
				iMin = Math.min(iMin, iValue);
			
			
			sTooltip = "<div class='charttooltip'>value:" + iValue + sMax + "<br>" + dDate.toString() + "</i></div>";
			
			oData.addRow([dDate, iValue, iItemMax, sTooltip]);
		}
		iMax = Math.max(iMax, iMaxObserved);
		iAvgObs = Math.round(iSum/poJson.data.length);
		
		// set the display range of the chart to match the requested timerange
		oElement.empty();
		
		// buttons the the left of the chart ----------------------------------------------
		var oSpan = $("<SPAN>", {class:"chartbuttonpanel"});
		
		var oButton = $("<button>",{class:"csv_button",title:"download as CSV"}).button({icon:"ui-icon-document"});
		oButton.click(		function(){ oThis.onClickCSV()}		);
		oSpan.append(oButton);
		
		if (oOptions.showZoom){
			var oButton = $("<button>",{class:"csv_button",title:"Zoom"}).button({icon:"ui-icon-zoomin"});
			oButton.click(		function(){ oThis.onClickZoom()}		);
			oSpan.append(oButton);
		}

		if (oOptions.showCompare){
			var oButton = $("<button>",{class:"csv_button",title:"Compare"}).button({icon:"ui-icon-shuffle"});
			oButton.click(		function(){ oThis.onClickCompare()}		);
			oSpan.append(oButton);
		}

		if (oOptions.goUrl){
			var oButton = $("<button>",{class:"csv_button",title:oOptions.goCaption}).button({icon:"ui-icon-arrowreturn-1-n"});
			oButton.click(		function(){ oThis.onClickGo()}		);
			oSpan.append(oButton);
		}
		oElement.append(oSpan);		
		
		// draw the chart ----------------------------------------------------------------
		var sChartID=oElement.attr("id")+"chart";
		var oChartDiv= $("<SPAN>",{
			id:sChartID, 	class:"chartgraph",
			width:oOptions.width-this.consts.BUTTON_WIDTH-30, height:oOptions.height -5
		});
		oElement.append(oChartDiv);
		
		var oDiv = oChartDiv[0];
		var dStart = new Date(poJson.epoch_start);
		var dEnd = new Date(poJson.epoch_end);
		if (oOptions.previous_period == 1){
			var iDiff = poJson.epoch_end - poJson.epoch_start;
			dEnd = new Date(poJson.epoch_start);
			dStart = new Date(dEnd - iDiff);
		}
		
		oChart = new google.visualization.LineChart( oDiv );
		var oChartOptions = {
			title: oOptions.title,
			legend: "right",
			tooltip: {
				isHtml: true,
				style:{pointerEvents:'none'}
			},
			pointSize: 3,
			dataOpacity: 0.8,
			theme: 'maximized' ,
			hAxis: {
				textStyle:{color: 'DarkCyan'},
				viewWindow:{
					min:dStart,
					max:dEnd,
				}			
			},
			series: {
				0: {targetAxisIndex: 0, color:"blue", visibleInLegend:false},
				1: {targetAxisIndex: 1, color:"red", visibleInLegend:false}
			},
			vAxis: {
				0: {title: 'avg', textStyle: {color: 'blue'}},
				1: {title: 'max', textStyle: {color: 'red'}}
			},
			
			interpolateNulls: false
		};
		oChart.draw(oData, oChartOptions);
		
		//display maximumes and observed values --------------------------------------
		var oInfoDiv = $("#"+oOptions.pr__lower_div );
		oInfoDiv.empty();
		oInfoDiv.append("Max: "+ iMax + "<br>");
		oInfoDiv.append("Avg: "+ iAvgObs + "<br>");
		oInfoDiv.append("Min: "+ iMin + "<br>");
	}
});