<?php
/**
 * GeoJSON class : a geojson reader/writer.
 *
 * Note that it will always return a GeoJSON geometry. This
 * means that if you pass it a feature, it will return the
 * geometry of that feature strip everything else.
 */
class GeoJSON extends GeoAdapter
{
  /**
   * Given an object or a string, return a Geometry
   *
   * @param mixed $input The GeoJSON string or object
   *
   * @return object Geometry
   */
  public function read($input) {
    if (is_string($input)) {
      $input = json_decode($input);
    }
    if (!is_object($input)) {
      throw new Exception('Invalid JSON');
    }
    if (!is_string($input->type)) {
      throw new Exception('Invalid JSON');
    }

    // Check to see if it's a FeatureCollection
    if ($input->type == 'FeatureCollection') {
      $geoms = array();
      foreach ($input->features as $feature) {
        $geoms[] = $this->read($feature);
      }
      return geoPHP::geometryReduce($geoms);
    }

    // Check to see if it's a Feature
    if ($input->type == 'Feature') {
      return $this->read($input->geometry);
    }

    // It's a geometry - process it
    return $this->objToGeom($input);
  }

  private function objToGeom($obj) {
  
    $type = $obj->type;

    if ($type == 'GeometryCollection') {
      return $this->objToGeometryCollection($obj);
    }
    $method = 'arrayTo' . $type;
    return $this->$method($obj->coordinates);
  }

  private function arrayToPoint($array) {
  	
    return new Point($array[0], $array[1]);
  }

  private function arrayToLineString($array) {
    $points = array();
    foreach ($array as $comp_array) {
      $points[] = $this->arrayToPoint($comp_array);
    }
    return new LineString($points);
  }

  private function arrayToPolygon($array) {
    $lines = array();
    foreach ($array as $comp_array) {
      $lines[] = $this->arrayToLineString($comp_array);
    }
    return new Polygon($lines);
  }

  private function arrayToMultiPoint($array) {
    $points = array();
    foreach ($array as $comp_array) {
      $points[] = $this->arrayToPoint($comp_array);
    }
    return new MultiPoint($points);
  }

  private function arrayToMultiLineString($array) {
    $lines = array();
    foreach ($array as $comp_array) {
      $lines[] = $this->arrayToLineString($comp_array);
    }
    return new MultiLineString($lines);
  }

  private function arrayToMultiPolygon($array) {
    $polys = array();
    foreach ($array as $comp_array) {
      $polys[] = $this->arrayToPolygon($comp_array);
    }
    return new MultiPolygon($polys);
  }

  private function objToGeometryCollection($obj) {
    $geoms = array();
    if (empty($obj->geometries)) {
      throw new Exception('Invalid GeoJSON: GeometryCollection with no component geometries');
    }
    foreach ($obj->geometries as $comp_object) {
      $geoms[] = $this->objToGeom($comp_object);
    }
    return new GeometryCollection($geoms);
  }

  /**
   * Serializes an object into a geojson string
   *
   *
   * @param Geometry $obj The object to serialize
   *
   * @return string The GeoJSON string
   */
  public function write(Geometry $geometry, $return_array = FALSE) {
    if ($return_array) {
      return $this->getArray($geometry);
    }
    else {
      return json_encode($this->getArray($geometry));
    }
  }

  public function coordsPoint(Point $geometry) {
  	return array($geometry->getX(), $geometry->getY());
  }
  
  public function coordsCurve(Curve $geometry) {
  	$a = array();
  	foreach ( $geometry->getComponents() as $g ) {
  		$a[] = $this->coordsPoint($g);
  	}
  	return $a;
  }
  
  public function coordsSurface(Surface $geometry) {
  	$a = array();
  	foreach ( $geometry->getComponents() as $g ) {
  		$a[] = $this->coordsSurface($g);
  	}
  	return $a;
  }
  
  public function getArray(Geometry $geometry) {
 	  
  	  if ( $geometry instanceof Point ) {
  	  	return array(
  	  		'type' => $geometry->geometryType(),
  	  		'coordinates' => $this->coordsPoint($geometry)
  	  	);
  	  }
  	  
  	  if ( $geometry instanceof Curve ) {
  	  	return array(
  	  			'type' => $geometry->geometryType(),
  	  			'coordinates' => $this->coordsCurve($geometry)
  	  	);
  	  }
  	  
  	  if ( $geometry instanceof Polygon ) {
  	  	$coords = array();
  	  	$coords[] = $this->coordsCurve($geometry->exteriorRing());
  	  	$num = $geometry->numInteriorRings();
  	  	for ($i=0; $i<$num; $i++) { 
  	  		$coords[] = $this->coordsCurve($geometry->interiorRingN($i));
  	  	}
  	  	return array(
  	  			'type' => $geometry->geometryType(),
  	  			'coordinates' => $coords,
  	  	);
  	  }

  	  
  	  if ( $geometry instanceof GeometryCollection ) {
  	  	if ( get_class($geometry) != 'GeometryCollection' ) {
  	  		$coords = array();
  	  		$num = $geometry->numGeometries();
  	  		for ($i=1; $i<=$num; $i++) {
  	  			if ( $geometry instanceof Point ) {
  	  				$coords[] = $this->coordsPoint($geometry->geometryN($i));
  	  			}
  	  			else if ($geometry instanceof Curve ) {
  	  				$coords[] = $this->coordsCurve($geometry->geometryN($i));
  	  			}
  	  			else if ($geometry instanceof Surface ) {
  	  				$coords[] = $this->coordsSurface($geometry->geometryN($i));
  	  			}
  	  		}
  	  		return array(
  	  				'type' => $geometry->geometryType(),
  	  				'coordinates' => $coords,
  	  		);
  	  	}
  	  	
  	  	$coords = array();
  	  	$num = $geometry->numGeometries();
  	  	for ($i=1; $i<=$num; $i++) {
  	  		$coords[] = $this->getArray($geometry->geometryN($i));
  	  	}

  	  	return array(
  	  			'type' => $geometry->geometryType(),
  	  			'geometries' => $coords
  	  	); 	  	
  	  }

  }
  
}


