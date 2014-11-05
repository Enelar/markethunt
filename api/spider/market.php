<?php

class market extends api
{
  public function Price($id)
  {
    $parse = LoadModule('api/spider', 'parse');

    $code = <<<EOF
      var res = {};
      res.prices = [];
      $('.b-offers__list .b-old-prices__num')
        .each(function() 
        { 
          res.prices.push($(this).html().replace('&nbsp;', ''))
        });

      res.shops = [];
      $('.shop-link.b-address__link')
        .each(function()
        {
          res.shops.push($(this).html());
        });
      res.success = $('.product-tabs').size();
      res.name = $('.product-title h1').children().remove().end().text();
      ret.ymid = $('table.b-modelinfo').attr('id');
      ret = res;
EOF;

    $ret = $parse->ExecuteAgainst($id, $code);

    if ($ret['success'])
      if (!$this->IsModelKnown($ret['ymid']))
        db::Query("INSERT INTO market.models(ymid, name) VALUES ($1, $2)", [$ret['ymid'], $res['name']]);
    return $ret;
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
    if (!is_numeric($ymid))
      return false;
    $res = db::Query("INSERT INTO spider.tasks(url, realtime) VALUES ($1, true) RETURNING id",
      ["http://market.yandex.ru/offers.xml?how=aprice&modelid={$ymid}"], true);
  }
}