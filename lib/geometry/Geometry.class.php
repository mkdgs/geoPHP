<?php

/**
 * Geometry abstract class
 */
abstract class Geometry
{
	private   $geos = NULL;
	protected $srid = NULL;
	protected $geom_type;
	protected $dimension = 2; // or dimension ?
	protected $measured = false;

	public $components = array();

	/**
	 * Constructor: Checks and sets component geometries
	 *
	 * @param array $components array of geometries
	*/
	public function __construct($components = array()) {
		if (!is_array($components)) {
			throw new Exception("Component geometries must be passed as an array");
		}
		foreach ($components as $component) {
			if ($component instanceof Geometry) {
				$this->components[] = $component;
				if ($component->coordinateDimension() > $this->dimension) {
					$this->dimension = $component->coordinateDimension();
				}
			}
			else {
				throw new Exception("Cannot create a collection with non-geometries");
			}
		}
	}

	/*  ____________
	 *  BASIC METHOD
	*/

	/**
	 * Dimension ( ):Integer — The inherent dimension of this geometric object, which must be less than or equal
	 * to the coordinate dimension. This specification is restricted to geometries in 2-dimensional coordinate space.
	 */
	public function dimension() {
		$dimension = $this->dimension;
		foreach ($this->components as $component) {
			if ($component->dimension() > $dimension) {
				$dimension = $component->dimension();
			}
		}
		return $dimension;
	}

	/**
	 * GeometryType ( ):String — Returns the name of the instantiable subtype of Geometry of which this
	 * geometric object is a instantiable member. The name of the subtype of Geometry is returned as a string.
	 */
	public function geometryType() {
		return $this->geom_type;
	}

	/**
	 * SRID ( ):Integer — Returns the Spatial Reference System ID for this geometric object.
	 */
	public function SRID() {
		return $this->srid;
	}

	/**
	 * Envelope( ):Geometry — The minimum bounding box for this Geometry, returned as a Geometry. The
	 * polygon is defined by the corner points of the bounding box [(MINX, MINY), (MAXX, MINY), (MAXX, MAXY),
	 * (MINX, MAXY), (MINX, MINY)].
	 *
	 * @return Polygon
	 */
	public function envelope() {
		if ($this->isEmpty()) return new Polygon();

		if ($this->geos()) {
			return geoPHP::geosToGeometry($this->geos()->envelope());
		}

		$bbox = $this->getBBox();
		$points = array (
				new Point($bbox['maxx'],$bbox['miny']),
				new Point($bbox['maxx'],$bbox['maxy']),
				new Point($bbox['minx'],$bbox['maxy']),
				new Point($bbox['minx'],$bbox['miny']),
				new Point($bbox['maxx'],$bbox['miny']),
		);

		$outer_boundary = new LineString($points);
		return new Polygon(array($outer_boundary));
	}

	/**
	 * AsText( ):String —
	 *  Exports this geometric object to a specific Well-known Text Representation of Geometry.
	 */
	public function asText() {
		return $this->out('wkt');
	}

	/**
	 * AsBinary( ):Binary
	 *  Exports this geometric object to a specific Well-known Binary Representation of Geometry.
	 */
	public function asBinary() {
		return $this->out('wkb');
	}

	/**
	 * IsEmpty( ):Integer —
	 *  Returns 1 (TRUE) if this geometric object is the empty Geometry. If true, then this
	 *  geometric object represents the empty point set, ∅, for the coordinate space.
	 */
	abstract public function isEmpty();

	/**
	 * IsSimple( ):Integer —
	 * 	Returns 1 (TRUE) if this geometric object has no anomalous geometric points, such as
	 * 	self intersection or self tangency. The description of each instantiable geometric class will include the specific
	 * 	conditions that cause an instance of that class to be classified as not simple.
	 */
	public function isSimple() {
		if ($this->geos()) {
			return $this->geos()->isSimple();
		}

		// A collection is simple if all it's components are simple
		foreach ($this->components as $component) {
			if (!$component->isSimple()) return FALSE;
		}

		return TRUE;
	}

	/**
	 * Boundary( ):Geometry —
	 * Returns the closure of the combinatorial boundary of this geometric object
	 * (Reference [1], section 3.12.2). Because the result of this function is a closure, and hence topologically
	 * closed, the resulting boundary can be represented using representational Geometry primitives (Reference [1],
	 * section 3.12.2).
	 */
	public function boundary() {
		if ($this->isEmpty()) return new LineString();

		if ($this->geos()) {
			return $this->geos()->boundary();
		}

		$components_boundaries = array();
		foreach ($this->components as $component) {
			$components_boundaries[] = $component->boundary();
		}
		return geoPHP::geometryReduce($components_boundaries);
	}

	

	/*  ______________
	 *  RELATION METHOD
	 */

	public function equals(Geometry $geometry) {
		if ( $this->geos() ) {
			return $this->geos()->equals($geometry->geos());
		}

		// To test for equality we check to make sure that there is a matching point
		// in the other geometry for every point in this geometry.
		// This is slightly more strict than the standard, which
		// uses Within(A,B) = true and Within(B,A) = true
		// @@TODO: Eventually we could fix this by using some sort of simplification
		// method that strips redundant vertices (that are all in a row)

		$this_points = $this->getPoints();
		$other_points = $geometry->getPoints();

		// First do a check to make sure they have the same number of vertices
		if ( count($this_points) != count($other_points) ) {
			return FALSE;
		}

		foreach ($this_points as $point) {
			$found_match = FALSE;
			foreach ($other_points as $key => $test_point) {
				if ($point->equals($test_point)) {
					$found_match = TRUE;
					unset($other_points[$key]);
					break;
				}
			}
			if (!$found_match) {
				return FALSE;
			}
		}
		// All points match, return TRUE
		return TRUE;
	}

	public function explode() {
		$parts = array();
		foreach ($this->components as $component) {
			$parts = array_merge($parts, $component->explode());
		}
		return $parts;
	}

	/*
	 * abstract public function flatten(); // 3D to 2D
	*/
	public function flatten() {
		if ($this->dimension == 3) {
			$new_components = array();
			foreach ($this->components as $component) {
				$new_components[] = $component->flatten();
			}
			$type = $this->geometryType();
			return new $type($new_components);
		}
		return $this;
	}

	public function distance(Geometry $geometry) {
		if ($this->geos()) {
			return $this->geos()->distance($geometry->geos());
		}

		$distance = NULL;
		foreach ($this->components as $component) {
			$check_distance = $component->distance($geometry);
			if ($check_distance === 0) return 0;
			if ($check_distance === NULL) return NULL;
			if ($distance === NULL) $distance = $check_distance;
			if ($check_distance < $distance) $distance = $check_distance;
		}
		return $distance;
	}
	
	public function getBBox() {
		if ($this->isEmpty()) return NULL;

		if ($this->geos()) {
			$envelope = $this->geos()->envelope();
			if ($envelope->typeName() == 'Point') {
				return geoPHP::geosToGeometry($envelope)->getBBOX();
			}

			$geos_ring = $envelope->exteriorRing();
			return array(
					'maxy' => $geos_ring->pointN(3)->getY(),
					'miny' => $geos_ring->pointN(1)->getY(),
					'maxx' => $geos_ring->pointN(1)->getX(),
					'minx' => $geos_ring->pointN(3)->getX(),
			);
		}

		// Go through each component and get the max and min x and y
		$i = 0;
		foreach ($this->components as $component) {
			$component_bbox = $component->getBBox();

			// On the first run through, set the bbox to the component bbox
			if ($i == 0) {
				$maxx = $component_bbox['maxx'];
				$maxy = $component_bbox['maxy'];
				$minx = $component_bbox['minx'];
				$miny = $component_bbox['miny'];
			}

			// Do a check and replace on each boundary, slowly growing the bbox
			$maxx = $component_bbox['maxx'] > $maxx ? $component_bbox['maxx'] : $maxx;
			$maxy = $component_bbox['maxy'] > $maxy ? $component_bbox['maxy'] : $maxy;
			$minx = $component_bbox['minx'] < $minx ? $component_bbox['minx'] : $minx;
			$miny = $component_bbox['miny'] < $miny ? $component_bbox['miny'] : $miny;
			$i++;
		}

		return array(
				'maxy' => $maxy,
				'miny' => $miny,
				'maxx' => $maxx,
				'minx' => $minx,
		);
	}


	// Public: Standard -- Common to all geometries
	// --------------------------------------------
	public function setSRID($srid) {
		if ($this->geos()) {
			$this->geos()->setSRID($srid);
		}
		$this->srid = $srid;
	}

	public function coordinateDimension() {
		return $this->dimension;
	}


	/**
	 * check if is a 3D point
	 *
	 * @return true or NULL if is not a 3D point
	 */
	public function hasZ() {
		if ($this->dimension == 3) {
			return TRUE;
		}
	}
	
	/**
	 * set geometry have 3d value
	 *
	 * @param bool
	 */
	public function set3d($bool) {
		$this->dimension = ($bool) ? 3 : 2;
	}

	/**
	 * check if is a measured value
	 *
	 * @return true or NULL if is a measured value
	 */
	public function isMeasured() {
		return $this->measured;
	}

	/**
	 * set geometry have measured value
	 *
	 * @param bool
	 */
	public function setMeasured($bool) {
		$this->measured = ($bool) ? true : false;
	}

	// Public: Non-Standard -- Common to all geometries
	// ------------------------------------------------

	/**
	 * Returns Collection component geometries
	 *
	 * @deprecated will be set protected
	 * @return array
	 */
	public function getComponents() {
		return $this->components;
	}

	// $this->out($format, $other_args);
	public function out() {
		$args = func_get_args();

		$format = array_shift($args);
		$type_map = geoPHP::getAdapterMap();
		$processor_type = $type_map[$format];
		$processor = new $processor_type();

		array_unshift($args, $this);
		$result = call_user_func_array(array($processor, 'write'), $args);

		return $result;
	}

	/**
	 * @deprecated 
		public function geometryType() {
		return $this->geometryType();
	} */

	public function getSRID() {
		return $this->SRID();
	}

	
	
	public function is3D() {
		return $this->hasZ();
	}

	// Public: GEOS Only Functions
	// ---------------------------
	public function geos() {
		// If it's already been set, just return it
		if ($this->geos && geoPHP::geosInstalled()) {
			return $this->geos;
		}
		// It hasn't been set yet, generate it
		if (geoPHP::geosInstalled()) {
			$reader = new GEOSWKBReader();
			$this->geos = $reader->readHEX($this->out('wkb',TRUE));
		}
		else {
			$this->geos = FALSE;
		}
		return $this->geos;
	}
	
	public function getGeos() {
		return $this->geos();
	}

	public function setGeos($geos) {
		$this->geos = $geos;
	}

	public function pointOnSurface() {
		if ($this->geos()) {
			return geoPHP::geosToGeometry($this->geos()->pointOnSurface());
		}
	}

	public function equalsExact(Geometry $geometry) {
		if ($this->geos()) {
			return $this->geos()->equalsExact($geometry->geos());
		}
	}

	public function relate(Geometry $geometry, $pattern = NULL) {
		if ($this->geos()) {
			if ($pattern) {
				return $this->geos()->relate($geometry->geos(), $pattern);
			}
			else {
				return $this->geos()->relate($geometry->geos());
			}
		}
	}

	public function checkValidity() {
		if ($this->geos()) {
			return $this->geos()->checkValidity();
		}
	}

	public function buffer($distance) {
		if ($this->geos()) {
			return geoPHP::geosToGeometry($this->geos()->buffer($distance));
		}
	}

	public function intersection(Geometry $geometry) {
		if ($this->geos()) {
			return geoPHP::geosToGeometry($this->geos()->intersection($geometry->geos()));
		}
	}

	public function convexHull() {
		if ($this->geos()) {
			return geoPHP::geosToGeometry($this->geos()->convexHull());
		}
	}

	public function difference(Geometry $geometry) {
		if ($this->geos()) {
			return geoPHP::geosToGeometry($this->geos()->difference($geometry->geos()));
		}
	}

	public function symDifference(Geometry $geometry) {
		if ($this->geos()) {
			return geoPHP::geosToGeometry($this->geos()->symDifference($geometry->geos()));
		}
	}

	// Can pass in a geometry or an array of geometries
	public function union(Geometry $geometry) {
		if ($this->geos()) {
			if (is_array($geometry)) {
				$geom = $this->geos();
				foreach ($geometry as $item) {
					$geom = $geom->union($item->geos());
				}
				return geoPHP::geosToGeometry($geos);
			}
			else {
				return geoPHP::geosToGeometry($this->geos()->union($geometry->geos()));
			}
		}
	}

	public function simplify($tolerance, $preserveTopology = FALSE) {
		if ($this->geos()) {
			return geoPHP::geosToGeometry($this->geos()->simplify($tolerance, $preserveTopology));
		}
	}

	public function disjoint(Geometry $geometry) {
		if ($this->geos()) {
			return $this->geos()->disjoint($geometry->geos());
		}
	}

	public function touches(Geometry $geometry) {
		if ($this->geos()) {
			return $this->geos()->touches($geometry->geos());
		}
	}

	public function intersects(Geometry $geometry) {
		if ($this->geos()) {
			return $this->geos()->intersects($geometry->geos());
		}
	}

	public function crosses(Geometry $geometry) {
		if ($this->geos()) {
			return $this->geos()->crosses($geometry->geos());
		}
	}

	public function within(Geometry $geometry) {
		if ($this->geos()) {
			return $this->geos()->within($geometry->geos());
		}
	}

	public function contains(Geometry $geometry) {
		if ($this->geos()) {
			return $this->geos()->contains($geometry->geos());
		}
	}

	public function overlaps(Geometry $geometry) {
		if ($this->geos()) {
			return $this->geos()->overlaps($geometry->geos());
		}
	}

	public function covers(Geometry $geometry) {
		if ($this->geos()) {
			return $this->geos()->covers($geometry->geos());
		}
	}

	public function coveredBy(Geometry $geometry) {
		if ($this->geos()) {
			return $this->geos()->coveredBy($geometry->geos());
		}
	}

	public function hausdorffDistance(Geometry $geometry) {
		if ($this->geos()) {
			return $this->geos()->hausdorffDistance($geometry->geos());
		}
	}

	public function project(Geometry $point, $normalized = NULL) {
		if ($this->geos()) {
			return $this->geos()->project($point->geos(), $normalized);
		}
	}


	// bad methods
/*

	public function length() {
		$length = 0;
		foreach ($this->components as $delta => $component) {
			$length += $component->length();
		}
		return $length;
	}

	public function length3D() {
		$length = 0;
		foreach ($this->components as $delta => $component) {
			$length += $component->length3D();
		}
		return $length;
	}
*/


}