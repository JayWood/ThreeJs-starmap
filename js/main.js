var container, scene, camera, renderer, controls, stats, mesh;

init();
animate();

function init(){
	scene = new THREE.Scene();

	var screenwidth  = window.innerWidth,
		screenheight = window.innerHeight,
		view_angle   = 45,
		aspect       = screenwidth/screenheight,
		near         = 0.1,
		far          = 20000;

	camera = new THREE.PerspectiveCamera( view_angle, aspect, near, far );
	
	scene.add( camera );

	camera.position.set( 0, 150, 400 );
	camera.lookAt( scene.position );

	renderer = ( Detector.webgl ) ? new THREE.WebGLRenderer() : new THREE.CanvasRenderer();
	renderer.setSize( screenwidth, screenheight );

	container = document.getElementById( 'ThreeJs' );
	container.appendChild( renderer.domElement );

	THREEx.WindowResize( renderer, camera );
	THREEx.FullScreen.bindKey( { charCode : 'm'.charCodeAt(0) } );

	controls = new THREE.TrackballControls( camera );

	// Particles
	var particleCount = 7500,
	particles = new THREE.Geometry(),
	texture = new THREE.ImageUtils.loadTexture( "img/spark.png" );
	texture.depthWrite = false;

	var pMaterial = new THREE.PointCloudMaterial({
			color: 0xFFFFFF,
			size: 20,
			map: texture,
			blending: THREE.AdditiveBlending,
			transparent: true,
		});

	for( var p = 0; p < particleCount; p++ ){
		var px = Math.random() * 500 - 250,
			py = Math.random() * 500 - 250,
			pz = Math.random() * 500 - 250;

		particles.vertices.push( new THREE.Vector3( px, py, pz ) );
	}

	var particleSystem = new THREE.PointCloud( particles, pMaterial );
		particleSystem.sortParticles = true;

	scene.add( particleSystem );
}

function animate(){
	requestAnimationFrame( animate );
	render();
	update();
}

function update(){
	controls.update();
}

function render(){
	renderer.render( scene, camera );
}

/*var scene 		= new THREE.Scene(),
	camera 		= new THREE.PerspectiveCamera( 75, window.innerWidth / window.innerHeight, 0.1, 1000 ),
	renderer 	= new THREE.WebGLRenderer();

renderer.setSize( window.innerWidth, window.innerHeight );
document.body.appendChild( renderer.domElement );

var particleCount = 7500,
	particles = new THREE.Geometry(),
	texture = new THREE.ImageUtils.loadTexture( "img/spark.png" );
	texture.depthWrite = false;

var pMaterial = new THREE.PointCloudMaterial({
		color: 0xFFFFFF,
		size: 20,
		map: texture,
		blending: THREE.AdditiveBlending,
		transparent: true,
	});

for( var p = 0; p < particleCount; p++ ){
	var px = Math.random() * 500 - 250,
		py = Math.random() * 500 - 250,
		pz = Math.random() * 500 - 250;

	particles.vertices.push( new THREE.Vector3( px, py, pz ) );
}

var particleSystem = new THREE.PointCloud( particles, pMaterial );
	particleSystem.sortParticles = true;

scene.add( particleSystem );

function render(){
	requestAnimationFrame( render );
	renderer.render( scene, camera );
}
render();*/