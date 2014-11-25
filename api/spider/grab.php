<?php

class grab extends api
{
  private function DirectRequest($url)
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

  public function Request($url)
  {
    $obj = $this->DirectRequest($url);
    if (phoxy_conf()["adminip"])
      var_dump($obj);

    if (is_string($obj))
    {
      db::Query("INSERT INTO spider.page_cache(url, status) VALUES ($1, $2)", [$url, $obj]);
      return false;
    }

    $res = db::Query("INSERT INTO spider.page_cache(url, data, img, headers) VALUES ($1, $2, decode($3, 'base64'), $4) RETURNING id",
      [$url, $obj['body'], $obj['shot'], json_encode($obj['headers'])], true);
    if (!$res)
      return false;
    return $res['id'];
  }

  protected function TestGrabShowScreenshot()
  {
    $obj = $this->DirectRequest("http://market.yandex.ru/offers.xml?how=aprice&page=2&modelid=10495457");
    if (!isset($obj['shot']))
      die("Grab failed");
    echo "<img src='data:image/png;base64,{$obj['shot']}' />";
  }

  protected function Capcha()
  {
    $obj = $this->DirectRequest("http://market.yandex.ru/offers.xml?how=aprice&page=2&modelid=10495457");
    if (!isset($obj['shot']))
      die("Grab failed");
    echo "<script src='//cdnjs.cloudflare.com/ajax/libs/jquery/2.1.1/jquery.min.js'></script>";
    echo "<img src='data:image/png;base64,{$obj['shot']}' />";
    echo $obj['body'];
    echo "
    <script>
      $('input[type=\"submit\"]').parents('form').attr('action', '/api/spider/grab/SendCapcha');
    </script>";
  }

  protected function SendCapcha( $a, $b, $c )
  {
    $a = rawurlencode($a);
    $b = rawurlencode($b);
    $c = rawurlencode($c);
  
    $u = "http://market.yandex.ru/checkcaptcha?";
    $u .= "key=$a&retpath=$b&rep=$c";
    echo $this->DirectRequest($u);
  }
}