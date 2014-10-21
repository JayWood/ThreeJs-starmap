<?php 

class eveDB{

	private $db

	function eveDB( $host, $db, $user, $pass ){
		$this->db = new PDO( "mysql:host=$host;dbname=$db", $user, $pass );
	}
}