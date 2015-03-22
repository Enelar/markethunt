<?php

class market extends api
{
  public function Price($id)
  {
    $parse = LoadModule('api/spider', 'parse');

    $code = <<<EOF
      var res = {};
      res.prices = [];

      wtf = $('.b-old-prices__num');      
      if (wtf.size() == 0)
        wtf = $('.snippet-card__price');
      wtf.each(function() 
        { 
          res.prices.push($(this).html().replace('&nbsp;', ''))
        });

      res.shops = [];

      wtf = $('.shop-link.b-address__link')
      if (wtf.size() == 0)
        wtf = $('.snippet-card__shop');
      wtf.each(function()
        {
          res.shops.push($(this).html());
        });
      
      res.success = $('.product-tabs').size();
      res.name = $('.title[itemprop="name"]').children().remove().end().text();
      if (res.name.length == 0)
        res.name = $('.product-title .title').children().remove().end().text();
      res.ymid = $('table.b-modelinfo').attr('id');
      ret = res;
EOF;

    $ret = $parse->ExecuteAgainst($id, $code);

    if ($ret['success'])
    {
      if (!isset($ret['ymid']))
      {
        $task = db::Query("SELECT * FROM spider.tasks WHERE id=(SELECT task FROM spider.page_cache WHERE id=$1)", [$id], true);
        $ret['ymid'] = $this->ExtractYMIDFromLink($task->url);
      }

      $this->AddParsedModel($id, $ret);
      $this->StorePriceSlice($id, $ret);
    }
    return $ret;
  }

  private function StorePriceSlice($id, $ret)
  {
    $prices = [];
    foreach ($ret['prices'] as $price)
      $prices[] = (int)str_replace([" ", " "], "", $price);

    db::Query("INSERT INTO market.price_cache(id, ymid, prices, shops) VALUES ($1, $2, $3::int2[], $4::varchar[])",
      [$id, $ret['ymid'], $prices, $ret['shops']]);
  }

  private function AddParsedModel( $id, $ret )
  {
    if ($this->IsModelKnown($ret['ymid']))
      return;
    db::Query("INSERT INTO market.models(ymid, name) VALUES ($1, $2)", [$ret['ymid'], $ret['name']]);
  }

  public function ExtractYMIDFromLink($str)
  {
    phoxy_protected_assert(
      strpos($str, "product/") > 0,
      [
        "data" =>
        [
          "error" => "
Не удалось распознать ссылку на модель.
(Вы можете нажать на вопрос, для демонстрации)",
        ]
      ]);

    list($a, $b) = explode("product/", $str, 2);
    if (strpos($b, "/") !== false)
      list($id, $garb) = explode("/", $b, 2);
    else
      $id = $b;

    return $id;
  }

  private function IsModelKnown($ymid)
  {
    $res = db::Query("SELECT * FROM market.models WHERE ymid=$1", [$ymid], true);
    return $res();
  }

  public function AddModel($ymid)
  {
    if ($this->IsModelKnown($ymid))
      return true;
    $res = db::Query("INSERT INTO spider.tasks(url, realtime) VALUES ($1, true) RETURNING id",
      [$this->YMIDLink($ymid)], true);
    return true;
  }

  public function YMIDLink($ymid)
  {
    if (!is_numeric($ymid))
      return false;
    return "http://market.yandex.ru/offers.xml?grhow=shop&how=aprice&np=1&modelid={$ymid}";
  }
}