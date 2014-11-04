<?php

class config
{
  private $config = false;

  public function __invoke()
  {
    if (!$this->config)
      $this->config = $this->init();
    return $this->config;
  }

  private function init()
  {
    $public =
    [
      'secret_location' => './../config.yaml',
      'config_location' => './config.yaml',
    ];

    include_once('utils/pg_wrap.php');

    $config = $public;
    $config = array_merge_recursive($config, yaml_parse_file($public['config_location']));
    $config = array_merge_recursive($config, yaml_parse_file($public['secret_location']));

    $config = new row_wraper($config);

    return $config;
  }
}

$config = new config();
function conf()
{
  global $config;
  return $config();
}