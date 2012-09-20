<?php 
require_once 'test_comon.php';

/**
 *  Basic methods on geometric objects
 */
class test_geometry {


	public function run() {
		
	}
	
	public function test($args, $results) {
	
	}
	/**
	 * Dimension ( ):Integer — The inherent dimension of this geometric object, which must be less than or equal
	 * to the coordinate dimension. This specification is restricted to geometries in 2-dimensional coordinate space.
	 */
	public function dimension() {

	}
	/**
	 * GeometryType ( ):String — Returns the name of the instantiable subtype of Geometry of which this
	 * geometric object is a instantiable member. The name of the subtype of Geometry is returned as a string.
	 */
	public function geometryType()  {

	}
	/**
	 * SRID ( ):Integer — Returns the Spatial Reference System ID for this geometric object.
	 */
	public function SRID()  {

	}
	/**
	 * Envelope( ):Geometry — The minimum bounding box for this Geometry, returned as a Geometry. The
	 *polygon is defined by the corner points of the bounding box [(MINX, MINY), (MAXX, MINY), (MAXX, MAXY),
	 * (MINX, MAXY), (MINX, MINY)].
	 */
	public function envelope()  {

	}
	/**
	 *	 AsText( ):String — Exports this geometric object to a specific Well-known Text Representation of Geometry.
	 */
	public function asText()  {

	}

	/**
	 * AsBinary( ):Binary
	 * Exports this geometric object to a specific Well-known Binary Representation of Geometry.
	 */
	public function asBinary()  {
			
	}

	/**
	 * IsEmpty( ):Integer —
	 * Returns 1 (TRUE) if this geometric object is the empty Geometry. If true, then this
	 geometric object represents the empty point set, ∅, for the coordinate space.
	 */
	public function isEmpty()  {

	}
	/**
	 * IsSimple( ):Integer —
	 * Returns 1 (TRUE) if this geometric object has no anomalous geometric points, such as
	 self intersection or self tangency. The description of each instantiable geometric class will include the specific
	 conditions that cause an instance of that class to be classified as not simple.
	 */
	public function isSimple()  {

	}
	/**
	 * Boundary( ):Geometry —
	 * Returns the closure of the combinatorial boundary of this geometric object
	 (Reference [1], section 3.12.2). Because the result of this function is a closure, and hence topologically
	 closed, the resulting boundary can be represented using representational Geometry primitives (Reference [1],
	 section 3.12.2).
	 */
	public function boundary()  {

	}


}