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
    LoadModule('api/spider', 'market')->AddModel($ymid);
    $res = db::Query("INSERT INTO track.entity(uid, ymid) VALUES ($1, $2) RETURNING ymid",
      [LoadModule('api', 'auth')->uid(), $ymid], true);
    return $res();
  }

  protected function GetList()
  {
    $res = db::Query("SELECT entity.*, name FROM track.entity, market.models WHERE entity.ymid=models.ymid");
    return
    [
      "design" => "cp/track/list",
      "data" => ["list" => $res]
    ];
  }
}