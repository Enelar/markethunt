<?php

class sheduler extends api
{
  protected function SheduleNextHour()
  {
    $models = db::Query("SELECT ymid 
        FROM track.warnings, track.entity
        WHERE entity.id=warnings.id
          AND silence_until < now()
        GROUP BY ymid");

    $market = LoadModule('api/spider', 'market');
    //$grab = LoadModule('api/spider', 'grab');
    foreach ($models as $model)
    {
      $link = $market->YMIDLink($model->ymid);
      db::Query("INSERT INTO spider.tasks(url) VALUES ($1) RETURNING id", [$link], true);
      //echo $grab->Request($link);
    }

    return $models();    
  }

  protected function PrepareEmail()
  {
    $checks = db::Query("SELECT 
      *
      FROM track.warnings, track.entity
      WHERE warnings.id=entity.id
        AND silence_until < now()
      ORDER BY uid, entity.id");

    foreach ($checks as $check)
    {
      if ($check->uid != $uid)
      {
        if (isset($params['email']))
          $this->SendEmail($params['email'], $mail, $count);

        $count = 0;
        $uid = $check->uid;
        $params = db::Query("SELECT email, company FROM public.users WHERE id=$1", [$uid], true);
        $mail = "";
      }
      if ($params->email == null)
        continue;

      $pos = $this->DeterminePosition($params->company, $check->ymid);
      $name = $this->Name($check->ymid);
      echo "{$params->company} {$name} [{$check->minplace} {$pos} {$check->maxplace}]<br>";
      
      if ($pos === false)
        $mail .= "{$name} не найден на первой странице!\n";
      else if ($pos > $check->maxplace)
        $mail .= "{$name} находится на {$pos} месте. (Выше $check->maxplace)\n";
      else if ($pos < $check->minplace)
        $mail .= "{$name} находится на {$pos} месте. (Ниже $check->minplace)\n";
      else
        continue;

      $count++;
      db::Query("UPDATE track.warnings SET silence_until = now() + every::interval");
    }

    if (isset($params['email']))
      $this->SendEmail($params['email'], $mail, $count);
  }

  private function SendEmail( $email, $body, $count )
  {
    var_dump($count);
    if ($count == 0)
      return;

    $res = mail(
      $email,
      "Проверка положения: {$count} позиций не на своем месте",
      $body,
      'From: MarketHunt.Автомат <no-reply@markethunt.ru>'
        ."\r\n".'Reply-To: support@markethunt.ru'
        // ."\r\n" .'X-Mailer: PHP/' . phpversion()
      );

    var_dump("ОТПРАВКА ПИСЬМА $email");
    var_dump($body);
    var_dump("===" . $res);

    $res = mail(
      "enelar@develop-project.ru",
      "Проверка положения: {$count} позиций не на своем месте",
      $body,
      'From: MarketHunt.Дублирование <no-reply@markethunt.ru>'
        ."\r\n".'Reply-To: support@markethunt.ru'
        // ."\r\n" .'X-Mailer: PHP/' . phpversion()
      );    
  }

  private function Name($ymid)
  {
    $res = db::Query("SELECT name FROM market.models WHERE ymid=$1", [$ymid], true);
    return $res->name;
  }

  private function DeterminePosition( $name, $ymid )
  {
    $cache = db::Query("SELECT 
      *
      FROM market.price_cache
      WHERE ymid=$1
        AND now() - snap < '4 hour'::interval
      ORDER BY snap DESC
      LIMIT 1", [$ymid], true);

    for ($i = 0; $i < count($cache->shops); $i++)
      if ($cache->shops[$i] == $name)
        return 1 + $i;

    return false;
  }
}