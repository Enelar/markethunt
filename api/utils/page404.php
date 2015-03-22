<?php

class page404 extends api
{
  protected function Reserve()
  {
    unset($this->addons['result']);
    return
    [
      "design" => "landing/page404/entry",
      "script" => "landing/landing",
      "before" => "landing.CleanUp"
    ];
  }
}