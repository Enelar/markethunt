<?php

class main extends api
{
  protected function Reserve()
  {
    unset($this->addons['result']);

    return
    [
      "design" => "main/body",
    ];
  }

  protected function Home()
  {
    //return LoadModule('api/cp/', 'track', true)->Reserve();
    unset($this->addons['result']);
    return $this('api/landing', 'request_demo', true)->Reserve();
  }
}