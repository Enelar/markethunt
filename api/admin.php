<?php

class admin extends api
{
  public function __construct()
  {
    phoxy_protected_assert(phoxy_conf()["ip"] == '213.21.7.16', ["error" => "Access denied"]);
    parent::__construct();
  }

  protected function SetUID($uid)
  {
    LoadModule('api', 'auth')->Login($uid);
  }
}