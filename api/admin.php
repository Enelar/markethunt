<?php

class admin extends api
{
  public function __construct()
  {
    phoxy_protected_assert(phoxy_conf()["ip"] == '213.21.7.6', ["error" => "Access denied"]);
    parent::__construct();
  }

  protected function SetUID($uid)
  {
    LoadModule('api', 'auth')->Login($uid);
  }

  protected function UpdateModelRequests()
  {
    $entity = db::Query("SELECT ymid FROM track.entity GROUP BY ymid");
    $count = 0;
    foreach ($entity as $lamer)
    {
      $res = db::Query("SELECT * FROM market.models WHERE ymid=$1", [$lamer->ymid], true);
      if ($res())
        continue;
      $count++;
      LoadModule('api/spider', 'market')->AddModel($lamer->ymid);
    }
    return $count;
  }

  protected function ConvertToJpeg($id)
  {
    $row = db::Query("SELECT img FROM spider.page_cache WHERE id=$1", [$id], true);
    if (!$row())
      return;

    $image = imagecreatefromstring($row->png);

    $bg = imagecreatetruecolor(imagesx($image), imagesy($image));
    imagefill($bg, 0, 0, imagecolorallocate($bg, 255, 255, 255));
    imagealphablending($bg, TRUE);
    imagecopy($bg, $image, 0, 0, 0, 0, imagesx($image), imagesy($image));
    imagedestroy($image);
    $quality = 50; // 0 = worst / smaller file, 100 = better / bigger file 

    ob_start();
    imagepng($bg, NULL, $quality);
    $store = ob_get_clean();
    imagedestroy($bg);

    db::Query("UPDATE spider.page_cache SET img=$2 WHERE id=$1", [$id, $store]);
  }

  protected function ForEveryConvert()
  {
    $rows = db::Query("SELECT id FROM spider.page_cache ORDER BY snap ASC");
    foreach ($rows as $row)
    {
      echo $row->id;
      echo "<br>";
      $this->ConvertToJpeg($row->id);
    }
  }
}