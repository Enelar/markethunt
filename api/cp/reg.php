<?php

class reg extends api
{
  protected function Show()
  {
    unset($this->addons['result']);
    return
    [
      "design" => "cp/reg/show",
    ];
  }

  protected function UpdateNotifyInfo( $what, $how )
  {
    if (!in_array($what, ["email", "company"]))
      return ["error" => "Ввод не распознан. Какие данные вы хотите изменить?"];

    $res = db::Query("UPDATE public.users SET {$what}=$2 WHERE id=$1", [LoadModule('api', 'auth')->uid(), $how], true);
    return $res();
  }

  protected function Modal()
  {
    $res = db::Query("SELECT email, company FROM public.users WHERE id=$1", [LoadModule('api', 'auth')->uid()], true);
    return
    [
      "design" => "cp/reg/modal",
      "data" => $res,
    ];
  }
}