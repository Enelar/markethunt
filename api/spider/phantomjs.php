<?php

function override_strpos($string, $needle, $offset = 0)
{
  if (!is_array($needle))
    return strpos($string, $needle, $offset);

  foreach ($needle as $need)
    if (($ret = strpos($string, $need, $offset)) !== false)
      return $ret;
  return false;
}

class phantomjs extends api
{
  public function Execute( $file, $arguments )
  {
    $args = [];
    foreach ($arguments as $a)
      $args[] = escapeshellarg($a);
    
    if (override_strpos($file, ["..", '"', "'", "\b"]) !== false)
      phoxy_protected_assert(false, ["error" => "Security bless you"]);

    $query = "phantomjs '{$file}' ".(implode(' ', $args));

    if (phoxy_conf()["adminip"])
    {
      var_dump($query);
      $res = system($query);
    }
    else
      $res = exec($query);

    return $res;
  }

  protected function Show( $id )
  {
    var_dump($code);
    $res = db::Query("SELECT id, md5(data) FROM spider.page_cache WHERE id=$1", [$id], true);

    if (!count($res))
      return "NOT FOUND";

  }
}