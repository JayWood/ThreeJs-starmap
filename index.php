<?php

/**
 * Testing for Three.js
 */

?>

<html>
	<head>
			<title>Testing</title>
			<script type="text/javascript" src="js/Detector.js"></script>
			<script type="text/javascript"><?php include( 'js_vars.php' ); ?></script>
	</head>
	<body>
		<?php 
		$x = -8.85119031484906e16;
		$y = -8.850925647176062e16
		?>
		<div id="ThreeJs"></div>
		<script type="text/javascript" src="lib/threejs/build/three.min.js"></script>
		<script type="text/javascript" src="js/TrackballControls.js"></script>
		<script type="text/javascript" src="js/Threex.FullScreen.js"></script>
		<script type="text/javascript" src="js/Threex.WindowResize.js"></script>

		<!-- My Script -->
		<script type="text/javascript" src="js/main.js"></script>
	</body>
</html>