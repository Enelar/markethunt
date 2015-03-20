<?php

class request_demo extends api
{
  protected function Reserve()
  {
    unset($this->addons['result']);
    return
    [
      "design" => "landing/request_demo/entry",
      "script" => "landing/landing",
      "before" => "landing.CleanUp"
    ];
  }
}