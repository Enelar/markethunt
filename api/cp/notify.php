<?php

class notify extends api
{
  protected function SetMinPosition( $id, $min )
  {
    return $this->UpdateWarningProperty($id, 'minplace', $min);
  }

  protected function SetMaxPosition( $id, $max )
  {
    return $this->UpdateWarningProperty($id, 'maxplace', $max);
  }

  protected function SetFrequency( $id, $freq )
  {
    return $this->UpdateWarningProperty($id, 'every', $freq);
  }

  private function UpdateWarningProperty( $id, $name, $value )
  {
    db::Query("UPDATE track.warnings SET {$name}=$2 WHERE id=$1", [$id, $value]);
  }
}