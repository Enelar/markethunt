<?php

class track extends api
{
  protected function Reserve()
  {
    return
    [
      "design" => "cp/track/index",
    ];
  }

  protected function Add($url)
  {
    return $this->AddToWatch($url);
  }

  private function AddToWatch($str)
  {
    $id = LoadModule('api/spider', 'market')->ExtractYMIDFromLink($str);
    return $this->AddTrack($id);
  }

  private function AddTrack($ymid)
  {
    $filter = [LoadModule('api', 'auth')->uid(), $ymid];
    $try = db::Query("SELECT * FROM track.entity WHERE uid=$1 AND ymid=$2", $filter, true);
    if ($try())
      return true;
    LoadModule('api/spider', 'market')->AddModel($ymid);
    $res = db::Query("INSERT INTO track.entity(uid, ymid) VALUES ($1, $2) RETURNING ymid", $filter, true);
    return $res();
  }

  protected function GetList()
  {
    $res = db::Query("SELECT entity.*, name FROM track.entity LEFT JOIN market.models ON entity.ymid=models.ymid WHERE uid=$1",
      [LoadModule('api', 'auth')->get_uid()]);
    return
    [
      "design" => "cp/track/list",
      "data" => ["list" => $res]
    ];
  }
}