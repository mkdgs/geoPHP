<?php
namespace geoPHP\Geometry\MultiPolygon;
/**
 * MultiPolygon: A collection of Polygons
 */
class MultiPolygon extends GeometryCollection
{
  protected $geom_type = 'MultiPolygon';
  protected $dimension = 2;
}
