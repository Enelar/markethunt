<?php

class parse extends api
{
  public function Milestone($id)
  {
    $row = db::Query("
      WITH one_row AS
      (
        SELECT * FROM spider.parse_tasks LIMIT 1
      ) DELETE FROM spider.parse_tasks
        USING one_row as b
        WHERE one_row.id=id
        RETURNING id", [], true);
    // Right now support only market
    $market = LoadModule('api/spider', 'market');
    $market->Price($row['id']);
  }

  public function ExecuteAgainst( $id, $code )
  {
    $result = "/tmp/spider_parse_".time();
      
    $pjs = LoadModule("api/spider", "phantomjs");
    $res = $pjs->Execute("api/spider/parse.js", [$id, $result, $code]);

    $return = file_get_contents($result);

    unlink($result);
    return json_decode($return, true);
  }
}