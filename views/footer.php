<script type="text/javascript" src="/static/java/jquery-3.4.1.min.js"></script>
<script type="text/javascript" src="/static/java/bootstrap.bundle.min.js"></script>
<script type="text/javascript" src="/static/java/form-validation.js"></script>
<script type="text/javascript" src="/static/java/java.js"></script>
<script async defer src="https://maps.googleapis.com/maps/api/js?key=api_key&callback=initMap&libraries=places" type="text/javascript"></script>
<script type="text/javascript" src="/static/java/googleMaps.js"></script>
<script>
	var address = '<?php print $geolocate; ?>';
	var action_type = '<?php print $type; ?>';
	lat = '<?php print $lat; ?>';
	lng = '<?php print $lng; ?>';
</script>
</body>
</html>