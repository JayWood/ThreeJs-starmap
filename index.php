<?php
	include 'functions.php';
?>
<html>
	<head>
			<title>Testing</title>
			<script type="text/javascript" src="js/Detector.js"></script>
			<script type="text/javascript">
				var map_center = <?php echo json_encode( $map_center ); ?>;
				var systems = <?php echo json_encode( $systems ); ?>;
			</script>
	</head>
	<body>
		<div class="header"><h1>Eve Starmap</h1></div>
		<div id="ThreeJs"></div>
		<script type="text/javascript" src="lib/threejs/build/three.min.js"></script>
		<script type="text/javascript" src="js/TrackballControls.js"></script>
		<script type="text/javascript" src="js/Threex.FullScreen.js"></script>
		<script type="text/javascript" src="js/Threex.WindowResize.js"></script>

		<!-- My Script -->
		<script type="text/javascript" src="js/main.js"></script>
	</body>
</html>