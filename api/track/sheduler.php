<?php

class sheduler extends api
{
  protected function SheduleNextHour()
  {
    $models = db::Query("SELECT ymid 
        FROM track.warnings, track.entity
        WHERE entity.id=warnings.id
          AND silence_until < now()
        GROUP BY ymid");

    $market = LoadModule('api/spider', 'market');
    //$grab = LoadModule('api/spider', 'grab');
    foreach ($models as $model)
    {
      $link = $market->YMIDLink($model->ymid);
      db::Query("INSERT INTO spider.tasks(url) VALUES ($1) RETURNING id", [$link], true);
      //echo $grab->Request($link);
    }

    return $models();    
  }
}