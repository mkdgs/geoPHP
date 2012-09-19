<?php
namespace geoPHP\Geometry\LineString;

/**
 * LineString. A collection of Points representing a line.
 * A line can have more than one segment.
 * A LineString is a Curve with linear interpolation between Points. Each consecutive pair of Points defines a Line
 * segment.
 */
class LineString extends Curve
{
  protected $geom_type = 'LineString';
}