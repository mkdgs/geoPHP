<?php 
/**
 * MultiPoint: A collection Points
 */
class MultiPoint extends GeometryCollection
{
  protected $geom_type = 'MultiPoint';

  public function isSimple() {
    return TRUE;
  }

}

