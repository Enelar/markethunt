<?php

class moveon extends api
{
  protected function DoIt()
  {
    $res = db::Query("
    WITH one_row AS
    (
      SELECT * FROM spider.tasks as b
        WHERE b.lock IS NULL
          AND b.procceed IS NULL
        ORDER BY id ASC
        LIMIT 1
    ) UPDATE spider.tasks as a
      SET \"lock\"=now() 
      FROM one_row as b
      WHERE b.id=a.id
      RETURNING *
      ", [], true);
    if (!$res)
      return null;
    $grab = LoadModule('api/spider', 'grab');
    $ret = $grab->Request($res['url']);

    db::Query("UPDATE spider.tasks SET procceed=now(), success=$2, lock=NULL WHERE id=$1", [$res['id'], !!$ret]);
    if (!!$ret)
      db::Query("INSERT INTO spider.parse_tasks(id) VALUES ($1)", [$ret]);
    return $ret;
  }
}