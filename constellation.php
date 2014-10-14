<!DOCTYPE HTML>
<html lang="en">
    <head>
        <title>Constellation display</title>
        <meta charset="utf-8">

        <style type="text/css">
            body {
                background-color: #000000;
                margin: 0px;
                overflow: hidden;
            }
        </style>
        
    </head>
    <body>
<div style="position:absolute;display:block;color:white">Click, hold and drag to rotate. right click to restart rotation. hover on a 'star' to see the name. mousewhere to zoom during rotation (as I'm still more than a little shaky on the camera positioning math)</div>
        <script src="lib/threejs/build/three.min.js"></script>

        <script>
<?php

require_once('db.inc.php');

$constellation=20000020;
if ( array_key_exists('constellation', $_GET ) && is_numeric( $_GET['constellation'] ) ){
    $constellation=$_GET['constellation'];
}


$sql="select max(x)-min(x) scaler,min(x) minx,min(y) miny,min(z) minz from mapSolarSystems where constellationid=?";
$stmt = $dbh->prepare($sql);
$stmt->execute(array($constellation));

$scaler = $minx = $miny = $minz = 0;

if ( $row = $stmt->fetchObject() ){
    $scaler=$row->scaler;
    $minx=$row->minx;
    $miny=$row->miny;
    $minz=$row->minz;
}

$sql="select distinct constellationid,solarsystemname,security,floor((x-:minx)/:scalar *420) x ,floor((y-:miny)/:scalar *420) y,floor((z-:minz)/:scalar *420) z,if(constellationid=:constellation,luminosity,0.1) luminosity  from mapSolarSystems,mapSolarSystemJumps where fromconstellationid=:constellation and (mapSolarSystemJumps.toSolarSystemID=solarsystemid or mapSolarSystemJumps.fromSolarSystemID=solarsystemid)";

$stmt = $dbh->prepare($sql);
$stmt->execute(array(":minx"=>$minx,":miny"=>$miny,":minz"=>$minz,":scalar"=>$scaler,":constellation"=>$constellation));

$stars="stars=[";
while ($row = $stmt->fetchObject())
{
$stars.="[".$row->x.",".$row->y.",".$row->z.',"'.$row->solarsystemname.'",'.$row->luminosity.",".$row->constellationid.'],';
}
$stars=trim($stars,",");

echo $stars."];";


$sql="select floor((mss1.x- :minx)/:scalar *420) fx ,floor((mss1.y- :miny)/:scalar *420) fy,floor((mss1.z- :minz)/:scalar *420) fz,floor((mss2.x- :minx)/:scalar *420) tx ,floor((mss2.y- :miny)/:scalar *420) ty,floor((mss2.z- :minz)/:scalar *420) tz  from mapSolarSystems mss1,mapSolarSystems mss2,mapSolarSystemJumps where mss1.solarsystemid=fromSolarSystemID and mss2.solarsystemid=toSolarSystemID and fromconstellationid=:constellation";


$stmt = $dbh->prepare($sql);
$stmt->execute(array(":minx"=>$minx,":miny"=>$miny,":minz"=>$minz,":scalar"=>$scaler,":constellation"=>$constellation));

$jumps="jumps=[";
while ($row = $stmt->fetchObject())
{
$jumps.="[".$row->fx.",".$row->fy.",".$row->fz.','.$row->tx.",".$row->ty.",".$row->tz."],";
}
$jumps=trim($jumps,",");
echo $jumps."];";


echo "constellation=".$constellation.";";

?>
// Edit this!
url="http://localhost/eve-starmap/constellation.html";
target = new THREE.Vector3( 0, 0, 0 );
arc=0;
radius=700;
var isMouseDown = false, onMouseDownPosition, theta = 45, onMouseDownTheta = 45, phi = 60, onMouseDownPhi = 60,    isShiftDown = false;
var rotate=true,intersectrotation=true;
var mouse = { x: 0, y: 0 }, INTERSECTED,position= { x: 0, y: 0 };


            var camera, scene, renderer,projector, particles = [];

            // let's get going! 
            init();

            function init() {

                // Camera params : 
                // field of view, aspect ratio for render output, near and far clipping plane. 
                camera = new THREE.PerspectiveCamera(80, window.innerWidth / window.innerHeight, 1, 4000 );
    
                // move the camera backwards so we can see stuff! 
                // default position is 0,0,0. 
                camera.position.z = 700;
                                camera.lookAt( target );
                // the scene contains all the 3D object data
                scene = new THREE.Scene();
                
                // camera needs to go in the scene 
                scene.add(camera);
    
                // and the CanvasRenderer figures out what the 
                // stuff in the scene looks like and draws it!
                renderer = new THREE.CanvasRenderer();
                renderer.setSize( window.innerWidth, window.innerHeight );
    
                // the renderer's canvas domElement is added to the body
                document.body.appendChild( renderer.domElement );
                projector = new THREE.Projector();
                makeParticles(); 
                document.addEventListener( 'mousemove', onDocumentMouseMove, false );
                document.addEventListener( 'mousedown', onDocumentMouseDown, false );
                document.addEventListener( 'mouseup', onDocumentMouseUp, false );
                document.addEventListener( 'mousewheel', onDocumentMouseWheel, false );
                document.addEventListener( 'DOMMouseScroll', onDocumentMouseWheel, false );
                onMouseDownPosition = new THREE.Vector2();
                // add the mouse move listener
                // render 30 times a second (should also look 
                // at requestAnimationFrame) 
                //setInterval( update, 1000 / 30); 
                renderer.domElement.oncontextmenu= function(){ return false;} 
            }

            // the main update function, called 30 times a second

            /*function update() {
                        

                var vector = new THREE.Vector3( mouse.x, mouse.y, 1 );
                projector.unprojectVector( vector, camera );
                //var ray = new THREE.Ray( camera.position, vector.subSelf( camera.position ).normalize() );

                var intersects = ray.intersectObjects( scene.children );

                if ( intersects.length > 0 ) {
                        if ( INTERSECTED != intersects[ 0 ].object ) {
                             INTERSECTED = intersects[ 0 ].object;
                          document.getElementById("test").style.left=(position.x+30)+"px"; 
                          document.getElementById("test").style.top=(position.y-5)+"px";
                          document.getElementById("test").innerHTML=INTERSECTED.name;
                          document.getElementById("test").style.display="block";
                          intersectrotation=false;
                        }
                }
                else
                {
                        document.getElementById("test").style.display="none";
                          intersectrotation=true;
                }

                if (rotate&intersectrotation)
                { 
                      arc=(arc>6.28)? 0 : arc+0.01;
                      arcx=Math.floor(Math.cos(arc)*radius);
                      arcz=Math.floor(Math.sin(arc)*radius);
                      camera.position.x=arcx;
                      camera.position.z=arcz;
                }                       
                camera.lookAt( target );
                renderer.render( scene, camera );

            }*/

            
            function makeParticles() { 
                
                var particle, material; 
                var geometry = new THREE.SphereGeometry( 10,16,16);
                for ( var star in stars ) {
                    color=stars[star][4] * 0xffffff;
                    if (stars[star][5] != constellation)
                    {
                    color=0x333333;
                    } 
                    var particle = new THREE.Mesh( geometry, new THREE.MeshLambertMaterial( { color: color } ) );
                    particle.position.x = stars[star][0]-210;
                    particle.position.y = stars[star][1]-210;
                    particle.position.z = stars[star][2]-210;
                    particle.name=stars[star][3];
                    particle.constellation=stars[star][5];
                    scene.add( particle );
                    particles.push(particle); 
                }
                var material = new THREE.LineBasicMaterial( { color: 0xcccccc, opacity: 0.4, linewidth: 1 } );
                
                for (var jump in jumps){
                     var geometry = new THREE.Geometry();
                     geometry.vertices.push( new THREE.Vector3(jumps[jump][0]-210, jumps[jump][1]-210, jumps[jump][2]-210 ) );
                     geometry.vertices.push( new THREE.Vector3(jumps[jump][3]-210, jumps[jump][4]-210, jumps[jump][5]-210 ) );
                     var line = new THREE.Line( geometry,  material );
                     scene.add(line);
                }

                
            }
            

            function particleRender( context ) {
                
                context.beginPath();
                context.arc( 0, 0, 1, 0,  Math.PI * 2, true );
                context.fill();
            };


            function onDocumentMouseUp( event ) {

                event.preventDefault();
  
                isMouseDown = false;
                onMouseDownPosition.x = event.clientX - onMouseDownPosition.x;
                onMouseDownPosition.y = event.clientY - onMouseDownPosition.y;

                if (event.which==3) {
                    rotate=true;
                }

                if ( onMouseDownPosition.length() > 5 ) {
                    return;
                }

                //update();

            }



            function onDocumentMouseDown( event ) {

                event.preventDefault();

                isMouseDown = true;
                rotate=false;

                onMouseDownTheta = theta;
                onMouseDownPhi = phi;
                onMouseDownPosition.x = event.clientX;
                onMouseDownPosition.y = event.clientY;


                var vector = new THREE.Vector3( mouse.x, mouse.y, 1 );
                projector.unprojectVector( vector, camera );
                //var ray = new THREE.Ray( camera.position, vector.subSelf( camera.position ).normalize() );

                var intersects = ray.intersectObjects( scene.children );

                if ( intersects.length > 0 ) {
                          if (intersects[ 0 ].object.constellation!=constellation){
                              window.location=url+"?constellation="+INTERSECTED.constellation;
                        }
                }

            }

            function onDocumentMouseMove( event ) {

                event.preventDefault();
                mouse.x = ( event.clientX / window.innerWidth ) * 2 - 1;
                mouse.y = - ( event.clientY / window.innerHeight ) * 2 + 1;
                position.x=event.clientX;
                position.y=event.clientY;
                if ( isMouseDown ) {

                    theta = - ( ( event.clientX - onMouseDownPosition.x ) * 0.5 ) + onMouseDownTheta;
                    phi = ( ( event.clientY - onMouseDownPosition.y ) * 0.5 ) + onMouseDownPhi;

                    phi = Math.min( 180, Math.max( 0, phi ) );

                    camera.position.x = radius * Math.sin( theta * Math.PI / 360 ) * Math.cos( phi * Math.PI / 360 );
                    camera.position.y = radius * Math.sin( phi * Math.PI / 360 );
                    camera.position.z = radius * Math.cos( theta * Math.PI / 360 ) * Math.cos( phi * Math.PI / 360 );
                    camera.updateMatrix();
                    //update();
                }

            }

         function onDocumentMouseWheel( event ) {

                if (event.detail)
                {
                           radius -= event.detail*10;
                }
                if (event.wheelDelta)
                {
                     radius -=event.wheelDelta; 
                }
                //update();

        }            
        </script>
<div id="test" style="position:absolute;z-index:10;color:white;display:none"></div>
    </body>
</html>
