<?php

class main extends api
{
  protected function Reserve()
  {
    unset($this->addons['result']);
    return
    [
      "design" => "body"
    ];
  }
}