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
      ORDER BY realtime DESC
      RETURNING *
      ", [], true);
    if (!$res)
      return null;
    $grab = LoadModule('api/spider', 'grab');
    $ret = $grab->Request($res['url']);

    $rt = db::Query("UPDATE spider.tasks SET procceed=now(), success=$2, lock=NULL WHERE id=$1 RETURNING realtime", [$res['id'], !!$ret]);
    if (!!$ret)
      db::Query("INSERT INTO spider.parse_tasks(id, realtime) VALUES ($1)", [$ret, $rt->realtime]);
    return $ret;
  }
}