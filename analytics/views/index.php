<?php if (isset($gcode) && trim($gcode) != '') : ?>
<script>
var _gaq=[['_setAccount','<?php echo $gcode; ?>'],['_trackPageview'],['_trackPageLoadTime']];
(function(d,t){var g=d.createElement(t),s=d.getElementsByTagName(t)[0];
	g.src=('https:'==location.protocol?'//ssl':'//www')+'.google-analytics.com/ga.js';
	s.parentNode.insertBefore(g,s)}(document,'script')
);
</script>
<?php endif; ?>