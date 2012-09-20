<?php 
require_once 'test_comon.php';
run_test();
function run_test() {
	foreach (scandir('./input') as $file) {
		print '---- Testing '.$file."\n";
		$parts = explode('.',$file);
		if ($parts[0]) {
			$format = $parts[1];
			$value = file_get_contents('./input/'.$file);
			
			$geometry = geoPHP::load($value, $format);
			test_adapters($geometry, $format, $value);
			//test_methods($geometry);
			test_geometry_basics($geometry);
			//test_detection($value, $format, $file);
		}
	}
	print "Testing Done!";
}

function test_geometry_basics($geometry) {
	// Place holders
	$geometry->hasZ();
	$geometry->is3D();
	$geometry->isMeasured();
	$geometry->isEmpty();
	
	// Test common functions	
	$geometry->boundary();
	$geometry->envelope();
	$geometry->getBBox();
	$geometry->isSimple();
	$geometry->asText();
	$geometry->asBinary();

	$geometry->dimension();
	$geometry->geometryType();
	$geometry->SRID();
	$geometry->setSRID(4326);

	// Aliases	
	$geometry->geometryType();
	$geometry->getSRID();
	
	// GEOS only functions
	//$geometry->getGeos();
	//$geometry->geos();
	//$geometry->setGeos($geometry->geos());
}

function test_geometry_relation() {
	$geometry->pointOnSurface();
	$geometry->equals($geometry);
	$geometry->equalsExact($geometry);
	$geometry->relate($geometry);
	$geometry->checkValidity();
	
	$geometry->buffer(10);
	$geometry->intersection($geometry);
	$geometry->convexHull();
	$geometry->difference($geometry);
	$geometry->symDifference($geometry);
	$geometry->union($geometry);
	$geometry->simplify(0);// @@TODO: Adjust this once we can deal with empty geometries
	$geometry->disjoint($geometry);
	$geometry->touches($geometry);
	$geometry->intersects($geometry);
	$geometry->crosses($geometry);
	$geometry->within($geometry);
	$geometry->contains($geometry);
	$geometry->overlaps($geometry);
	$geometry->covers($geometry);
	$geometry->coveredBy($geometry);
	$geometry->centroid();
}

function test_geometry_analysis() {
	$geometry->distance($geometry);
	$geometry->hausdorffDistance($geometry);
	$geometry->length();
	$geometry->greatCircleLength();
	$geometry->haversineLength();
}


function test_geometry_point() {
	$geometry->y();
	$geometry->x();
	$geometry->getX();
	$geometry->getY();
	$geometry->z();
	$geometry->m();
	$geometry->coordinateDimension();
}

function test_geometry_line() {	
	$geometry->startPoint();
	$geometry->endPoint();
	$geometry->isRing();
	$geometry->isClosed();
	$geometry->numPoints();
	$geometry->pointN(1);
	
	// polygon
	$geometry->exteriorRing();
	$geometry->numInteriorRings();
	$geometry->interiorRingN(1);
	
	// geometry collection
	$geometry->numGeometries();
	$geometry->geometryN(1);	
}

function test_surface() {
	$geometry->getCentroid();
	$geometry->getArea();
	$geometry->area();
}

function test_adapters($geometry, $format, $input) {
	// Test adapter output and input. Do a round-trip and re-test
	foreach ( geoPHP::getAdapterMap() as $adapter_key => $adapter_class) {
		if ($adapter_key != 'google_geocode') { //Don't test google geocoder regularily. Uncomment to test
			print '------ '.$format.' to '.$adapter_key."\n";
			$output = $geometry->out($adapter_key);
			$adapter_class = ''.$adapter_class;
			if ($output) {
				 
				$adapter_loader = new $adapter_class();
				$test_geom_1 = $adapter_loader->read($output);
				$test_geom_2 = $adapter_loader->read($test_geom_1->out($adapter_key));

				if ($test_geom_1->out('wkt') != $test_geom_2->out('wkt')) {
					print "Mismatched adapter output in ".$adapter_class."\n";
				}
			}
			else {
				print "Empty output on "  . $adapter_key . "\n";
			}
		}
	}
}

// Test to make sure adapter work the same wether GEOS is ON or OFF
// Cannot test methods if GEOS is not intstalled
function test_geos_adaptaters() { 

	if ( geoPHP::geosInstalled()) return;
	
	foreach ( geoPHP::getAdapterMap() as $adapter_key => $adapter_class) {
		if ($adapter_key != 'google_geocode') { //Don't test google geocoder regularily. Uncomment to test
			// Turn GEOS on
			geoPHP::geosInstalled(TRUE);
	
			$output = $geometry->out($adapter_key);
			if ($output) {
				$adapter_loader = new $adapter_class();
	
				$test_geom_1 = $adapter_loader->read($output);
	
				// Turn GEOS off
				geoPHP::geosInstalled(FALSE);
	
				$test_geom_2 = $adapter_loader->read($output);
	
				// Turn GEOS back On
				geoPHP::geosInstalled(TRUE);
	
				// Check to make sure a both are the same with geos and without
				if ($test_geom_1->out('wkt') != $test_geom_2->out('wkt')) {
					print "Mismatched adapter output between GEOS and NORM in ".$adapter_class."\n";
				}
			}
		}
	}
}


function test_methods($geometry) {
	// Cannot test methods if GEOS is not intstalled
	if ( geoPHP::geosInstalled()) return;

	$methods = array(
			//'boundary', //@@TODO: Uncomment this and fix errors
			'envelope',   //@@TODO: Testing reveales errors in this method -- POINT vs. POLYGON
			'getBBox',
			'x',
			'y',
			'startPoint',
			'endPoint',
			'isRing',
			'isClosed',
			'numPoints',
	);

	foreach ($methods as $method) {
		// Turn GEOS on
	 geoPHP::geosInstalled(TRUE);
		$geos_result = $geometry->$method();

		// Turn GEOS off
	 geoPHP::geosInstalled(FALSE);
		$norm_result = $geometry->$method();

		// Turn GEOS back On
	 geoPHP::geosInstalled(TRUE);

		$geos_type = gettype($geos_result);
		$norm_type = gettype($norm_result);

		if ($geos_type != $norm_type) {
			print 'Type mismatch on '.$method."\n";
			continue;
		}

		// Now check base on type
		if ($geos_type == 'object') {
			$haus_dist = $geos_result->hausdorffDistance(geoPHP::load($norm_result->out('wkt'),'wkt'));

			// Get the length of the diagonal of the bbox - this is used to scale the haustorff distance
			// Using Pythagorean theorem
			$bb = $geos_result->getBBox();
			$scale = sqrt((($bb['maxy'] - $bb['miny'])^2) + (($bb['maxx'] - $bb['minx'])^2));

			// The difference in the output of GEOS and native-PHP methods should be less than 0.5 scaled haustorff units
			if ($haus_dist / $scale > 0.5) {
				print 'Output mismatch on '.$method.":\n";
				print 'GEOS : '.$geos_result->out('wkt')."\n";
				print 'NORM : '.$norm_result->out('wkt')."\n";
				continue;
			}
		}

		if ($geos_type == 'boolean' || $geos_type == 'string') {
			if ($geos_result !== $norm_result) {
				print 'Output mismatch on '.$method.":\n";
				print 'GEOS : '.(string) $geos_result."\n";
				print 'NORM : '.(string) $norm_result."\n";
				continue;
			}
		}

		//@@TODO: Run tests for output of types arrays and float
		//@@TODO: centroid function is non-compliant for collections and strings
	}
}

function test_detection($value, $format, $file) {
	$detected = geoPHP::detectFormat($value);
	if ($detected != $format) {
		if ($detected) print 'detected as ' . $detected . "\n";
		else print "not \n";
	}
	// Make sure it loads using auto-detect
 geoPHP::load($value);
}