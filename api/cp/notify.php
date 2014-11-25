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
    $owner = db::Query("SELECT * FROM track.entity WHERE id=$1 AND uid=$2", [$id, LoadModule('api', 'auth')->uid()], true);
    if (!$owner())
      return ["error" => "Недостаточно прав"];
    db::Query("UPDATE track.warnings SET {$name}=$2 WHERE id=$1", [$id, $value]);
  }

  protected function NotificationEmailKnown()
  {
    $res = db::Query("SELECT email FROM users WHERE id=$1", [LoadModule('api', 'auth')->uid()], true);

    return
    [
      "design" => "cp/track/email_check",
      "data" => ["NotificationEmailKnown" => $res->email != null]
    ];
  }
}