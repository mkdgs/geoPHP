<?php
/**
 * MultiPoint: A collection Points
 */
class MultiPoint extends Collection
{
  protected $geom_type = 'MultiPoint';

  /**
   * @deprecated is a linestring methods, use numGeometries 
   */
  public function numPoints() {
    return $this->numGeometries();
  }

  public function isSimple() {
    return TRUE;
  }

}

