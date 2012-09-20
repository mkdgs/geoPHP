<?php 
/**
 * GeometryCollection: A heterogenous collection of geometries  
 */
class GeometryCollection extends Geometry
{
  protected $geom_type = 'GeometryCollection';
  
  public function isEmpty() {
  	if (!count($this->components)) return TRUE;
  	foreach ($this->components as $component) {
  		if (!$component->isEmpty()) return FALSE;
  	}
  	return TRUE;
  }
  
  public function numGeometries() {
  	return count($this->components);
  }
  
  // Note that the standard is 1 based indexing
  public function geometryN($n) {
  	$n = intval($n);
  	if (array_key_exists($n-1, $this->components)) {
  		return $this->components[$n-1];
  	}
  	else {
  		return NULL;
  	}
  }
  
  
}