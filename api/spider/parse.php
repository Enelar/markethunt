<?php

class parse extends api
{
  protected function ExecuteAgainst( $id, $code )
  {
    $result = "/tmp/spider_parse_".time();
      
    $pjs = LoadModule("api/spider", "phantomjs");
    $res = $pjs->Execute("api/spider/parse.js", [$id, $result, $code]);

    $return = file_get_contents($result);

    unlink($result);
    return json_decode($return, true);
  }
}