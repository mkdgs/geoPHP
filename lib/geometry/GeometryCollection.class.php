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
  
  
  
  // We need to override asArray. Because geometryCollections are heterogeneous
  // we need to specify which type of geometries they contain. We need to do this
  // because, for example, there would be no way to tell the difference between a
  // MultiPoint or a LineString, since they share the same structure (collection
  // of points). So we need to call out the type explicitly. 
  /*
  public function asArray() {
    $array = array();
    foreach ($this->components as $component) {
      $array[] = array(
        'type' => $component->geometryType(),
        'components' => $component->asArray(),
      );
    }
    return $array;
  }*/
  
}