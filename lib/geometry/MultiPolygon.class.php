<?php
/**
 * MultiPolygon: A collection of Polygons
 */
class MultiPolygon extends GeometryCollection
{
  protected $geom_type = 'MultiPolygon';
  protected $dimention = 2;
}
