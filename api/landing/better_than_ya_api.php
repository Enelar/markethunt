<?php

class better_than_ya_api extends api
{
  public function __construct()
  {
    parent::__construct();
  }
  protected function Reserve()
  {

    return
    [
      "design" => "landing/better_than_ya_api/entry",
      "script" => "landing/landing",
      "before" => "landing.CleanUp"
    ];
  }
}