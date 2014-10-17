<?php

error_reporting(E_ALL);
ini_set('display_errors','On');

function phoxy_conf()
{
  $ret = phoxy_default_conf();
  global $_SERVER;
  $ret["ip"] = $_SERVER["HTTP_X_REAL_IP"];
  return $ret;
}

function default_addons( $name )
{
  $ret =
  [
    "cache" => ["no"],
    "result" => "canvas",
  ];
  return $ret;
}

include('phoxy/index.php');