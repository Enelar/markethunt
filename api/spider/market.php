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
      ret = res;
EOF;

    $ret = $parse->ExecuteAgainst($id, $code);
    return $ret;
  }
}