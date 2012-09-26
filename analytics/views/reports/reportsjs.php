<?php //		 head.ready(function(){
?>


			var so = new SWFObject("<?php echo base_url()?>bonfire/modules/analytics/assets/js/amcharts/amline.swf", "amline", "100%", "250", "8", "#FFFFFF");
			so.addVariable("data_file", encodeURIComponent("<?php echo base_url()?>bonfire/modules/analytics/assets/js/amcharts/amline_data.xml"));
			so.addVariable("chart_id", "amline");
			so.addParam("wmode", "opaque");
			so.addVariable('wmode', 'opaque');
			so.addVariable("settings_file", encodeURIComponent("<?php  echo base_url()?>bonfire/modules/analytics/assets/js/amcharts/amline_settings.xml"));
			so.addVariable("path", '<?php echo base_url()?>');
			so.write('linechart');
  
