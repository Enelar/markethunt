<?php

class market extends api
{
  protected function Price($id)
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
      res.success = $('.b-model-tabs').size();
      res.name = $('.b-page-title__title').children().remove().end().text();
      ret = res;
EOF;

    return $parse->ExecuteAgainst($id, $code);
  }
}