var scene 		= new THREE.Scene(),
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
render();