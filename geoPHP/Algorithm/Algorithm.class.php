<?php 
class Algorithm {
	
	//meters
	public function greatCircleLength($radius = 6378137) {
		$length = 0;
		foreach ($this->components as $component) {
			$length += $component->greatCircleLength($radius);
		}
		return $length;
	}
	
	// degree
	public function haversineLength() {
		$length = 0;
		foreach ($this->components as $component) {
			$length += $component->haversineLength();
		}
		return $length;
	}
	
	/**
	 * Double — The length of this Curve in its associated spatial reference.
	 */
	public function length() {
		if ($this->geos()) {
			return $this->geos()->length();
		}
		$length = 0;
		foreach ($this->getPoints() as $delta => $point) {
			$previous_point = $this->geometryN($delta);
			if ($previous_point) {
				$length += sqrt(pow(($previous_point->getX() - $point->getX()), 2) + pow(($previous_point->getY()- $point->getY()), 2));
			}
		}
		return $length;
	}
	
	public function distance(Geometry $geometry) {
		if ($this->geos()) {
			return $this->geos()->distance($geometry->geos());
		}
	
		if ($geometry->geometryType() == 'Point') {
			// This is defined in the Point class nicely
			return $geometry->distance($this);
		}
		if ($geometry->geometryType() == 'LineString') {
			$distance = NULL;
			foreach ($this->explode() as $seg1) {
				foreach ($geometry->explode() as $seg2) {
					if ($seg1->lineSegmentIntersect($seg2)) return 0;
					// Because line-segments are straight, the shortest distance will occur at an endpoint.
					// If they are parallel an endpoint calculation is still accurate.
					$check_distance_1 = $seg1->pointN(1)->distance($seg2);
					$check_distance_2 = $seg1->pointN(2)->distance($seg2);
					$check_distance_3 = $seg2->pointN(1)->distance($seg1);
					$check_distance_4 = $seg2->pointN(2)->distance($seg1);
	
					$check_distance = min($check_distance_1, $check_distance_2, $check_distance_3, $check_distance_4);
					if ($distance === NULL) $distance = $check_distance;
					if ($check_distance < $distance) $distance = $check_distance;
				}
			}
			return $distance;
		}
		else {
			// It can be treated as collection
			return parent::distance($geometry);
		}
	}
}