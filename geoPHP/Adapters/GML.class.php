<?php
namespace geoPHP\Adapters\GoogleGeocode;
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
 * @todo add gml:id 
 * 
 * 
 * PHP Geometry/Gml 3 encoder/decoder
 * http://www.opengeospatial.org/standards/gml
 * 
 * http://gis13.nsgc.gov.ns.ca/sns_webclient/WebHelp/GML_Input_Files.htm
 * http://www.tridas.org/documents/xmldocs/1.2/tridas2.html#id60
 */
class GML extends GeoAdapter {
	protected $namespace 	= 'gml'; // Name-space string. eg 'gml'
	protected $srsname 		= 'EPSG:4326'; // @todo set the uri

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
			
		$type = strtolower($geom->geometryType());
		switch ($type) {
			case 'point':
				$this->writePoint($geom, $xmlobj);
				break;
			case 'linestring':
				$this->writeLineString($geom, $xmlobj);
				break;
			case 'polygon':
				$this->writePolygon($geom, $xmlobj);
				break;
			case 'multipoint':
				$this->writeMultipoint($geom, $xmlobj);
				break;
			case 'multilinestring':
				$this->writeMultiLineString($geom, $xmlobj);
				break;
			case 'multipolygon':
				$this->writeMultiPolygon($geom, $xmlobj);
				break;
			case 'geometrycollection':
				$this->writeMultiGeometry($geom, $xmlobj);
				break;
		}
		// add on first srsName=\"$this->srsname\"
		// we need namespace, always
		// $element = $xmlobj->createElement('shema');
		// $element->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:gml', "http://www.opengis.net/gml");
		return $xmlobj->saveXML($xmlobj);
	}
	
	protected function ownerDocument(DOMNode $node) { 
		if ( $node->ownerDocument ) return $node->ownerDocument;
		return $node;
	}
	
	// pos must be direct position (lat long for wsg84)
	// http://portal.opengeospatial.org/files/?artifact_id=11606
	protected function writePoint(Point $geom, DOMNode $node) {
		$position = $geom->getX().' '. $geom->getY().(( $geom->hasZ() ) ? ' '.$geom->z() : '');
		$point = $this->ownerDocument($node)->createElement($this->namespace. ':Point');			
		$pos   = $this->ownerDocument($node)->createElement($this->namespace. ':pos');
		$pos->nodeValue = $position;
		$point->appendChild($pos);
		$node->appendChild($point);
		return $point;
	}
	
	protected function writeLineString(LineString $geom, DOMNode $node) {		
		$position = array();
		foreach ($geom->getComponents() as $k => $point) {			
			$position[] = $point->getX().' '. $point->getY().(( $geom->hasZ() ) ? ' '.$point->z() : '');			
		}
		$position = implode(',', $position); // @todo check poslist syntaxe im not sure, i think is bad
		
		$linestring  = $this->ownerDocument($node)->createElement($this->namespace. ':LineString');
		$poslist     = $this->ownerDocument($node)->createElement($this->namespace. ':posList', $position);
		$dimension   = $this->ownerDocument($node)->createAttribute($this->namespace. ':srsDimension');
		$dimension->value = ( $geom->hasZ() ) ? 3 : 2;
		
		$poslist->setAttributeNodeNS($dimension);
		$linestring->appendChild($poslist);
		$node->appendChild($linestring);
		return $linestring;
	}
	
	protected function writeLinearRing(LineString $geom, DOMNode $node) {
		$position = array();
		foreach ($geom->getComponents() as $k => $point) {
			$position[] = $point->getX().' '. $point->getY().( $geom->hasZ() ) ? ' '.$point->z() : '';
		}
		$position = implode(',', $position);
	
		$linestring  = $this->ownerDocument($node)->createElement($this->namespace. ':LinearRing');
		$poslist     = $this->ownerDocument($node)->createElement($this->namespace. ':posList', $position);
		$dimension   = $this->ownerDocument($node)->createAttribute($this->namespace. ':srsDimension');
		$dimension->value = ( $geom->hasZ() ) ? 3 : 2;
	
		$poslist->setAttributeNodeNS($dimension);
		$linestring->appendChild($poslist);
		$node->appendChild($linestring);
		return $linestring;
	}

	
	protected function writePolygon(Polygon $geom, DOMNode $node) {
		$polygon   = $this->ownerDocument($node)->createElement($this->namespace. ':polygon');
		$exterior  = $this->ownerDocument($node)->createElement($this->namespace. ':exterior');		
		
		$exterior_ring = $geom->exteriorRing();
		if ( $exterior_ring->numPoints() ) {
			$polygon->appendChild($this->writeLinearRing($exterior_ring, $exterior));
		}
		
		$num_interior_rings = $geom->numInteriorRings();
		if ( $num_interior_rings ) {			
			for ( $i=0; $i<$num_interior_rings; $i++) {
				$interior  = $this->ownerDocument($node)->createElement($this->namespace. ':interior');
				$polygon->appendChild($this->writeLinearRing($geom->interiorRingN($i), $interior));
			}
		}
		$node->appendChild($polygon);	
		return $polygon;
	}
	
	protected function writeMultipoint(MultiPoint $geom, DOMNode $node) {
		$multipoint   = $this->ownerDocument($node)->createElement($this->namespace. ':MultiPoint');			
		$num_points = $geom->numGeometries();
		if ( $num_points ) {
			for ( $i=0; $i<$num_points; $i++) {
				$pointMember   = $this->ownerDocument($node)->createElement($this->namespace. ':pointMember');
				$multipoint->appendChild($this->writePoint($geom->geometryN($i), $pointMember));
			}
		}
		return $multipoint;
	}
	
	protected function writeMultiLineString(MultiLineString $geom, DOMNode $node) { 
		$multicurve   = $this->ownerDocument($node)->createElement($this->namespace. ':MultiCurve');
		$num_curves = $geom->numGeometries();
		if ( $num_curves ) {
			for ( $i=0; $i<$num_curves; $i++) {
				$curveMember   = $this->ownerDocument($node)->createElement($this->namespace. ':curveMember');
				$multicurve->appendChild($this->writePoint($geom->geometryN($i), $curveMember));
			}
		}
		return $multicurve;
	}	
	
	protected function writeMultiPolygon(MultiPolygon $geom, DOMNode $node) { 
		$multipolygon   = $this->ownerDocument($node)->createElement($this->namespace. ':MultiPolygon');
		$num_polygons = $geom->numGeometries();
		if ( $num_polygons ) {
			for ( $i=0; $i<$num_polygons; $i++) {
				$polygonMember   = $this->ownerDocument($node)->createElement($this->namespace. ':polygonMember');
				$multipolygon->appendChild($this->writePoint($geom->geometryN($i), $polygonMember));
			}
		}
		return $multipolygon;
	}
	
	protected function writeMultiGeometry(Collection $geom, DOMNode $node) { 
		$multiGeometry   = $this->ownerDocument($node)->createElement($this->namespace. ':MultiGeometry');
		$num_geometry = $geom->numGeometries();
		if ( $num_geometry ) {
			for ( $i=0; $i<$num_geometry; $i++) {
				$geometryMember   = $this->ownerDocument($node)->createElement($this->namespace. ':geometryMember');
				$multiGeometry->appendChild($this->writePoint($geom->geometryN($i), $geometryMember));
			}
		}
		return $multiGeometry;
	}
	
	
/*
	protected function metaDataProperty() {
	
	}
<gml:MultiGeometry gml:id="ID">
   <gml:metaDataProperty>
      <gml:GenericMetaData>Any text, intermingled with:
         <!--any element-->
      </gml:GenericMetaData>
   </gml:metaDataProperty>
   <gml:description>string</gml:description>
   <gml:descriptionReference/>
   <gml:identifier codeSpace="http://www.example.com/">string</gml:identifier>
   <gml:name>string</gml:name>
   <gml:geometryMember>
   </gml:geometryMember>
   <gml:geometryMembers>
   </gml:geometryMembers>
</gml:MultiGeometry>



linestring deprecated in gml 3
	<gml:MultiCurve gml:id="ID">
   <gml:metaDataProperty>
      <gml:GenericMetaData>Any text, intermingled with:
         <!--any element-->
      </gml:GenericMetaData>
   </gml:metaDataProperty>
   <gml:description>string</gml:description>
   <gml:descriptionReference/>
   <gml:identifier codeSpace="http://www.example.com/">string</gml:identifier>
   <gml:name>string</gml:name>
   <gml:curveMember>
      <gml:LineString gml:id="ID">
         <gml:metaDataProperty>...
         </gml:metaDataProperty>
         <gml:description>string</gml:description>
         <gml:descriptionReference/>
         <gml:identifier codeSpace="http://www.example.com/">string</gml:identifier>
         <gml:name>string</gml:name>
         <gml:pos>1.0 1.0</gml:pos>
      </gml:LineString>
   </gml:curveMember>
   <gml:curveMembers>
      <gml:LineString gml:id="ID">
         <gml:metaDataProperty>...
         </gml:metaDataProperty>
         <gml:description>string</gml:description>
         <gml:descriptionReference/>
         <gml:identifier codeSpace="http://www.example.com/">string</gml:identifier>
         <gml:name>string</gml:name>
         <gml:pos>1.0 1.0</gml:pos>
      </gml:LineString>
   </gml:curveMembers>
</gml:MultiCurve>
	*/
	
/*
 * <gml:MultiPoint gml:id="ID">
   <gml:metaDataProperty>
      <gml:GenericMetaData>Any text, intermingled with:
         <!--any element-->
      </gml:GenericMetaData>
   </gml:metaDataProperty>
   <gml:description>string</gml:description>
   <gml:descriptionReference/>
   <gml:identifier codeSpace="http://www.example.com/">string</gml:identifier>
   <gml:name>string</gml:name>
   <gml:pointMember>
      <gml:Point gml:id="ID">
         <gml:metaDataProperty>...
         </gml:metaDataProperty>
         <gml:description>string</gml:description>
         <gml:descriptionReference/>
         <gml:identifier codeSpace="http://www.example.com/">string</gml:identifier>
         <gml:name>string</gml:name>
         <gml:pos>1.0 1.0</gml:pos>
      </gml:Point>
   </gml:pointMember>
   <gml:pointMembers>
      <gml:Point gml:id="ID">
         <gml:metaDataProperty>...
         </gml:metaDataProperty>
         <gml:description>string</gml:description>
         <gml:descriptionReference/>
         <gml:identifier codeSpace="http://www.example.com/">string</gml:identifier>
         <gml:name>string</gml:name>
         <gml:pos>1.0 1.0</gml:pos>
      </gml:Point>
   </gml:pointMembers>
</gml:MultiPoint> 


 */
}