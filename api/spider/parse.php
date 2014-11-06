<?php

class parse extends api
{
  protected function DoIt()
  {
    $row = db::Query("
      WITH one_row AS
      (
        SELECT * FROM spider.parse_tasks ORDER BY realtime DESC LIMIT 1
      ) DELETE FROM spider.parse_tasks
        USING one_row
        WHERE one_row.id=parse_tasks.id
        RETURNING parse_tasks.id", [], true);
    if (!$row())
      return false;
    return $this->Milestone($row['id']);
  }

  public function Milestone($id)
  {
    // Right now support only market
    $market = LoadModule('api/spider', 'market');
    return $market->Price($id);
  }

  public function ExecuteAgainst( $id, $code )
  {
    $result = "/tmp/spider_parse_".time();
      
    $pjs = LoadModule("api/spider", "phantomjs");
    $res = $pjs->Execute("api/spider/parse.js", [$id, $result, $code]);

    $return = file_get_contents($result);
    var_dump($return);

    //unlink($result);
    return json_decode($return, true);
  }
}