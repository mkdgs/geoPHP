<?php
/*
 * Copyright (c) Patrick Hayes
* Copyright (c) 2012 Desgranges Mickael
* Copyright (c) 2012 BCBGeo http://bcbgeo.com
*
* This code is open-source and licenced under the Modified BSD License.
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

/**
 * THIS CLASS IS NOT USABLE
 * 
 * 
 * PHP Geometry/Gml 3 encoder/decoder
 * http://www.opengeospatial.org/standards/gml
 */
class Gml extends GeoAdapter
{
	private $namespace 	= 'gml'; // Name-space string. eg 'gml'
	private $srsname 	= 'EPSG:4326'; // @todo set the uri

	/**
	 * Read GeoGml string into geometry objects
	 *
	 * @param string $gml - an XML
	 *
	 * @return Geometry|GeometryCollection
	 */
	public function read($gml) {
		return $this->geomFromText($gml);
	}

	/**
	 * Serialize geometries into a GeoRSS string.
	 *
	 * @param Geometry $geometry
	 *
	 * @return string The georss string representation of the input geometries
	 */
	public function write(Geometry $geometry) {
		return $this->geometryToGml($geometry);
	}

	
	/*
	public function geomFromText($xml) {
		// Change to lower-case, strip all CDATA
		$xml = mb_strtolower($xml, mb_detect_encoding($xml)); // why ?
		$xml = preg_replace('/<!\[cdata\[(.*?)\]\]>/s', '', $xml); // why ?

		// Load into DOMDOcument
		libxml_use_internal_errors(true);
		$xmlobj = new DOMDocument('1.0', 'UTF-8');
		@$xmlobj->loadXML($xml);
		// we need namespace, always
		if ( !$xmlobj->hasChildNodes() || !$xmlobj->firstChild->getAttributeNode('xmlns:georss') ) {
			$element = $xmlobj->createElement('feed');
			$element->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:georss', "http://www.georss.org/georss");

			$xmlobj->appendChild($element);

			foreach ( $xmlobj->childNodes as $child ) {
				if ( $element->isSameNode($child) ) continue;
				$element->appendChild($child);
			}

			$xml = $xmlobj->saveXml($xmlobj);
			$xmlobj->loadXML($xml);
		}

		$xmlobj = new DOMXPath($xmlobj);
		$xmlobj->registerNamespace('georss', "http://www.georss.org/georss");
		$pt_elements = $xmlobj->evaluate('//georss:point');

		$this->xmlobj = $xmlobj;

		try {
			$geom = $this->geomFromXML();
		} catch(InvalidText $e) {
			throw new Exception("Cannot Read Geometry From GeoRSS: ". $text);
		} catch(Exception $e) {
			throw $e;
		}

		return $geom;
	}
	

	protected function geomFromXML() {
		$geometries = array();
		$geometries = array_merge($geometries, $this->parsePoints());
		$geometries = array_merge($geometries, $this->parseLines());
		$geometries = array_merge($geometries, $this->parsePolygons());
		$geometries = array_merge($geometries, $this->parseBoxes());
		$geometries = array_merge($geometries, $this->parseCircles());

		if (empty($geometries)) {
			throw new Exception("Invalid / Empty GeoRSS");
		}

		return geoPHP::geometryReduce($geometries);
	}

	protected function getPointsFromCoords($string) {
		$coords = array();
		$latlon = explode(' ',$string);
		foreach ($latlon as $key => $item) {
			if (!($key % 2)) {
				// It's a latitude
				$lat = $item;
			}
			else {
				// It's a longitude
				$lon = $item;
				$coords[] = new Point($lon, $lat);
			}
		}
		return $coords;
	}

	protected function parsePoints() {
		$points = array();
		$pt_elements = $this->xmlobj->evaluate('//georss:point');
		foreach ($pt_elements as $pt) {
			$point_array = $this->getPointsFromCoords(trim($pt->firstChild->nodeValue));
			$points[] = $point_array[0];
		}
		return $points;
	}

	protected function parseLines() {
		$lines = array();
		$line_elements = $this->xmlobj->evaluate('//georss:line');
		foreach ($line_elements as $line) {
			$components = $this->getPointsFromCoords(trim($line->firstChild->nodeValue));
			$lines[] = new LineString($components);
		}
		return $lines;
	}

	protected function parsePolygons() {
		$polygons = array();
		$poly_elements = $this->xmlobj->evaluate('//georss:polygon');
		foreach ($poly_elements as $poly) {
			if ($poly->hasChildNodes()) {
				$points = $this->getPointsFromCoords(trim($poly->firstChild->nodeValue));
				$exterior_ring = new LineString($points);
				$polygons[] = new Polygon(array($exterior_ring));
			}
			else {
				// It's an EMPTY polygon
				$polygons[] = new Polygon();
			}
		}
		return $polygons;
	}

	// Boxes are rendered into polygons
	protected function parseBoxes() {
		$polygons = array();
		$box_elements = $this->xmlobj->evaluate('//georss:box');
		foreach ($box_elements as $box) {
			$parts = explode(' ',trim($box->firstChild->nodeValue));
			$components = array(
					new Point($parts[3], $parts[2]),
					new Point($parts[3], $parts[0]),
					new Point($parts[1], $parts[0]),
					new Point($parts[1], $parts[2]),
					new Point($parts[3], $parts[2]),
			);
			$exterior_ring = new LineString($components);
			$polygons[] = new Polygon(array($exterior_ring));
		}
		return $polygons;
	}

	// Circles are rendered into points
	// @@TODO: Add good support once we have circular-string geometry support
	protected function parseCircles() {
		$points = array();
		$circle_elements = $this->xmlobj->evaluate('//georss:circle');
		foreach ($circle_elements as $circle) {
			$parts = explode(' ',trim($circle->firstChild->nodeValue));
			$points[] = new Point($parts[1], $parts[0]);
		}
		return $points;
	}

	*/
	
	
	protected function geometryToGml($geom) {
		// Load into DOMDOcument
		libxml_use_internal_errors(true);
		$xmlobj = new DOMDocument('1.0', 'UTF-8');
		@$xmlobj->loadXML($xml);
		
			
			
		$type = strtolower($geom->getGeomType());
		switch ($type) {
			case 'point':
				return $this->writePoint($geom, $xmlobj);
				break;
			case 'linestring':
				return $this->linestringToGeoRSS($geom, $xmlobj);
				break;
			case 'polygon':
				return $this->PolygonToGeoRSS($geom, $xmlobj);
				break;
			case 'multipoint':
			case 'multilinestring':
			case 'multipolygon':
			case 'geometrycollection':
				return $this->writeCollection($geom, $xmlobj);
				break;
		}
		// add on first srsName=\"$this->srsname\"
		// we need namespace, always
		// $element = $xmlobj->createElement('shema');
		// $element->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:gml', "http://www.opengis.net/gml");
		return $xmlobj->saveXML();
	}


	
	// pos must be direct position (lat long for wsg84)
	// http://portal.opengeospatial.org/files/?artifact_id=11606
	protected function writePoint(Point $geom, DOMNode $node) {
		$position = $geom->getX().' '. $geom->getY().( $geom->hasZ() ) ? ' '.$geom->z() : '';
		$point = $node->ownerDocument->createElementNS($this->namespace, 'Point');			
		$pos   = $node->ownerDocument->createElementNS($this->namespace, 'pos', $position);
		$point->appendChild($pos);
		$node->appendChild($point);
		return $point;
	}
	
	private function writeLinestring(LineString $geom, DOMNode $node) {		
		$position = array();
		foreach ($geom->getComponents() as $k => $point) {			
			$position[] = $point->getX().' '. $point->getY().( $geom->hasZ() ) ? ' '.$point->z() : '';			
		}
		$position = implode(',', $position);
		
		$linestring  = $node->ownerDocument->createElementNS($this->namespace, 'LineString');
		$poslist     = $node->ownerDocument->createElementNS($this->namespace, 'posList', $position);
		$dimension   = $node->ownerDocument->createAttributeNS($this->namespace, 'srsDimension');
		$dimension->value = ( $geom->hasZ() ) ? 3 : 2;
		
		$poslist->setAttributeNodeNS($dimension);
		$linestring->appendChild($poslist);
		$node->appendChild($linestring);
		return $linestring;
	}
	
	private function writeLinearRing(LineString $geom, DOMNode $node) {
		$position = array();
		foreach ($geom->getComponents() as $k => $point) {
			$position[] = $point->getX().' '. $point->getY().( $geom->hasZ() ) ? ' '.$point->z() : '';
		}
		$position = implode(',', $position);
	
		$linestring  = $node->ownerDocument->createElementNS($this->namespace, 'LinearRing');
		$poslist     = $node->ownerDocument->createElementNS($this->namespace, 'posList', $position);
		$dimension   = $node->ownerDocument->createAttributeNS($this->namespace, 'srsDimension');
		$dimension->value = ( $geom->hasZ() ) ? 3 : 2;
	
		$poslist->setAttributeNodeNS($dimension);
		$linestring->appendChild($poslist);
		$node->appendChild($linestring);
		return $linestring;
	}

	
	private function writePolygon(Polygon $geom, DOMNode $node) {
		$output = '<'.$this->nss.'polygon>';		
		$polygon   = $node->ownerDocument->createElementNS($this->namespace, 'polygon');
		$exterior  = $node->ownerDocument->createElementNS($this->namespace, 'exterior');
		
		
		$exterior_ring = $geom->exteriorRing();
		if ( $exterior_ring->numPoints() ) {
			$polygon->appendChild($this->writeLinearRing($exterior_ring, $exterior));
		}
		
		$num_interior_rings = $geom->numInteriorRings();
		if ( $num_interior_rings ) {			
			for ( $i=0; $i<$num_interior_rings; $i++) {
				$interior  = $node->ownerDocument->createElementNS($this->namespace, 'interior');
				$polygon->appendChild($this->writeLinearRing($geom->interiorRingN($i), $interior));
			}
		}
		$node->appendChild($polygon);	
		return $polygon;
	}

}