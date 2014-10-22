<?php 

class eveDB{

	private $db;

	function eveDB( $host, $db, $user, $pass = '' ){
		$this->db = new PDO( "mysql:host=$host;dbname=$db", $user, $pass );
	}


	/**
	 * Get Scale Array
	 * Currently supports OBJECT and ARRAY_A return types.
	 * @return MIXED Returns the scale object
	 */
	public function get_scale( $return_type = OBJECT, $constellation = false, $region = false ){
		error_log( 'x' );
		$params = null;
		$scale_sql = "
			select max( x ) - min( x ) scaler,
		    min( x ) minx,
		    min( y ) miny,
		    min( z ) minz 
		    from mapSolarSystems
		    where securityClass <> ''
		";

		if( ! empty( $constellation ) ){
			$scale_sql .= "
		    	and constellationid=?
		    ";

		    $params = array( intval( $constellation ) );
		}

		$stmt = $this->db->prepare( $scale_sql );
		$stmt->execute( $params );
		$row = $stmt->fetchObject();

		if( !empty( $row ) ){
			switch( $return_type ){
				case ARRAY_A:
					return array(
						'scaler' => $row->scaler,
						'minx'   => $row->minx,
						'miny'   => $row->miny,
						'minz'   => $row->minz
					);
					break;
				default:
					return $row;
					break;
			}
		}

		return $stmt->errorinfo();
	}


	public function get_systems( $scalenum = 420, $constellation = false, $region = false ){
		$scale_arr = $this->get_scale( ARRAY_A, $constellation, $region );
		$scaler = $minx = $miny = $minz = 0;

		if( empty( $scale_arr ) ) return false;

		extract( $scale_arr );

		if( $scaler === 0 ){
			return $scale_arr; // Cannot divide by zero, duh!
		}

		$params = array(
			':minx'      => floatval( $minx ),
			':miny'      => floatval( $miny ),
			':minz'      => floatval( $minz ),
			':scaler'    => floatval( $scaler ),
			':scale_num' => floatval( $scalenum ),
		);

		$solar_systems = "
	    select distinct
	        constellationid,
	        solarsystemname,
	        security,
	        ( x - :minx ) / :scaler * :scale_num as x,
	        ( y - :miny ) / :scaler * :scale_num as y,
	        ( z - :minz ) / :scaler * :scale_num as z
	    ";

	    $solar_systems .= ( $constellation ) ? "
	    	if( constellationid = :constellation, luminosity, 0.1 ) ,luminosity
	    " : '';

	    $solar_systems .= "
	        from mapsolarsystems
	    ";

    	if( $constellation ){
    		// Set the constellation array key now, otherwise leave it alone.
    		$params[':constellation'] = $constellation;
    		$solar_systems .= "
	            ,mapSolarSystemJumps
		        where fromconstellationid=:constellation 
		        and ( mapSolarSystemJumps.toSolarSystemID = solarsystemid or mapSolarSystemJumps.fromSolarSystemID=solarsystemid )
		        and securityClass <> ''
	        ";
    	}else{
    		$solar_systems .= "
		    	where securityClass <> ''
		    ";
    	}

    	$stmt = $this->db->prepare( $solar_systems );
    	$stmt->execute( $params );
    	$results = array();
    	while( $row = $stmt->fetchObject() ){
    		$results[] = $row;
    	}

    	if( ! empty( $results ) ) return $results;

		return $stmt->errorinfo();
	}

	public function map_center( $constellation = false, $scale_num = 420 ){
		$scale = $this->get_scale( $constellation );

		if( empty( $scale ) ) return false;

		$scaler = $scale->scaler;


		$sql = "
			select
				max( x ) - min( x ) / 2000 / :scaler * :scale_num as x,
				max( y ) - min( y ) / 2000 / :scaler * :scale_num as y,
				max( z ) - min( z ) / 2000 / :scaler * :scale_num as z
			from mapSolarSystems
				where securityClass <> ''
		";

		if( ! empty( $constellation ) ){
			$sql .= "
		    	and constellationid = :constellation
		    ";
		}

		$constellation = ! empty( $constellation ) ? $constellation : null;

		$stmt = $this->db->prepare( $sql );
		$stmt->execute( array(
			':constellation' => $constellation,
			':scaler' => $scaler,
			':scale_num' => $scale_num,
		) );
		$row = $stmt->fetchObject();
		$result = !empty( $row ) ? $row : $stmt->errorinfo();

		return $result;
	}

	/**
	 * Get Solarsystem by Name
	 * @param  string $name Solarsystem name
	 * @return OBJ          Database Row Object
	 */
	public function get_system_by_name( $name ){

		$name = ucwords( $name ); // Make sure it's standard
		$sql = "
			select *
			from mapSolarSystems
			where solarSystemName=?
		";

		$stmt = $this->db->prepare( $sql );
		$stmt->execute( array( $name ) );
		$row = $stmt->fetchObject();
		if( ! empty( $row ) ) return $row;

		return false;
	}

	/**
	 * Get Solarsystem ID
	 * @param  string $name Solarsystem Name
	 * @return int          Solarsystem ID
	 */
	public function get_system_id( $name ){

		$system_obj = $this->get_system_by_name( $name );

		return ! empty( $system_obj ) && isset( $system_obj->solarSystemID ) ? $system_obj->solarSystemID : false;
	}

	/**
	 * Get Solarsystem Region Obj
	 * @param  string $name Solarsystem Name
	 * @return int          Solarsystem ID
	 */
	public function get_system_region_data( $name ){

		$system_obj = $this->get_system_by_name( $name );

		if( false === $system_obj ) return false;

		$region_id = $system_obj->regionID;

		$sql ="
			select *
			from mapregions
			where regionID = ?
		";

		$stmt = $this->db->prepare( $sql );
		$stmt->execute( array( $region_id ) );
		$row = $stmt->fetchObject();

		return ! empty( $row ) ? $row : false;
	}

	/**
	 * Get Region Data
	 * @param  int|string $region Region ID or Region Name
	 * @return obj                Database Row
	 */
	public function get_region_data( $region ){
		$sql = "
			select *
			from mapregions
		";

		if( is_int( $region ) ){
			$sql .="
				where regionID = ?
			";
		}else{
			$sql .="
				where regionName = ?
			";
		}

		$stmt = $this->db->prepare( $sql );
		$stmt->execute( array( $region ) );

		return ! empty( $row ) ? $row : false;
	}
}