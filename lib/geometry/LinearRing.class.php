<?php
/**
 * A LinearRing is a LineString that is both closed and simple. 
 * is a closed LineString that is a LinearRing.
 * is a closed LineString that is not a LinearRing.
 * @author mickael
 *
 */
class LinearRing extends Curve {
	protected $geom_type = 'LinearRing';
	
}