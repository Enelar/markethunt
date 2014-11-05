<?php

error_reporting(E_ALL);
ini_set('display_errors','On');

include_once('utils/config.php');

include_once('phpsql/phpsql.php');
include_once('phpsql/pgsql.php');
$sql = new phpsql();
$pg = $sql->Connect("pgsql://postgres@localhost/markethunt");
include_once('phpsql/wrapper.php');

include_once('phpsql/db.php');
db::Bind(new phpsql\utils\wrapper($pg));

function phoxy_conf()
{
  $ret = phoxy_default_conf();
  global $_SERVER;
  $ret["ip"] = $_SERVER["HTTP_X_REAL_IP"];
  $ret["adminip"] = $ret["ip"] == '213.21.7.6';
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