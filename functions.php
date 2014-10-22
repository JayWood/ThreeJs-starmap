<?php

require_once 'db.inc.php';

$db = new EveDB( 'localhost', 'sdecrius1', 'root' );
$constellation = isset( $_GET['constellation'] ) ? $_GET['constellation'] : false;
$system_results = $db->get_systems( 1000, $constellation );
$map_center = $db->map_center( $constellation );
$systems = ! empty( $system_results ) ? $system_results : false;