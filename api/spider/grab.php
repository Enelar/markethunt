<?php

class grab extends api
{
  protected function DirectRequest($url)
  {
    $result = tempnam("/tmp", "spider_grab_");

    $pjs = LoadModule("api/spider", "phantomjs");
    $res = $pjs->Execute("api/spider/grab.js", [$result, $url]);

    $return = file_get_contents($result);
    
    unlink($result);

    $obj = json_decode($return, true);
    if ($obj)
      return $obj;
    return $res;
  }

  protected function Request($url)
  {
    $obj = $this->DirectRequest($url);
    if (phoxy_conf()["adminip"])
      var_dump($obj);

    if (is_string($obj))
    {
      db::Query("INSERT INTO spider.page_cache(url, status) VALUES ($1, $2)", [$url, $obj]);
      return false;
    }

    db::Query("INSERT INTO spider.page_cache(url, data, img, headers) VALUES ($1, $2, decode($3, 'base64'), $4)",
      [$url, $obj['body'], $obj['shot'], json_encode($obj['headers'])]);
    return true;
  }
}