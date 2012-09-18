<?php

/**
 * Collection: Abstract class for compound geometries
 *
 * A geometry is a collection if it is made up of other
 * component geometries. Therefore everything but a Point
 * is a Collection. For example a LingString is a collection
 * of Points. A Polygon is a collection of LineStrings etc.
 */
abstract class Surface extends Geometry
{


	public function isEmpty() {
		if (!count($this->components)) return TRUE;
		return false;
	}

  public function centroid() {
    if ($this->isEmpty()) return NULL;

    if ($this->geos()) {
      $geos_centroid = $this->geos()->centroid();
      if ($geos_centroid->typeName() == 'Point') {
        return geoPHP::geosToGeometry($this->geos()->centroid());
      }
    }

    // As a rough estimate, we say that the centroid of a colletion is the centroid of it's envelope
    // @@TODO: Make this the centroid of the convexHull
    // Note: Outside of polygons, geometryCollections and the trivial case of points, there is no standard on what a "centroid" is
    $centroid = $this->envelope()->centroid();

    return $centroid;
  }


  public function area() {
    if ($this->geos()) {
      return $this->geos()->area();
    }

    $area = 0;
    foreach ($this->components as $component) {
      $area += $component->area();
    }
    return $area;
  }


}