<?php

class auth extends api
{
  public function uid()
  {
    phoxy_protected_assert($this->is_user_authorized(), ["error" => "Auth required"]);
    return $this->get_uid();
  }

  public  function get_uid()
  {
    global $_session;

    if (isset($_SESSION['uid']))
      return $_SESSION['uid'];
    return false;
  }

  public function is_user_authorized()
  {
    return !!$this->get_uid();
  }

  public function login($id)
  {
    return $this->get_login($id);
  }

  private function get_login($id = null)
  {
    if (session_status() !== PHP_SESSION_ACTIVE)
      session_start();
    global $_SESSION;

    //undefined index 'uid' без 43 и 44 строки, при подтверждении email

    if(!is_null($id))
      $_SESSION['uid'] = $id;
    return $_SESSION['uid'];
  }

  protected function logout()
  {
    $this->login(0);
    return
    [
      'reset' => true,
    ];
  }

  private function get_forced_uid()
  {
    if ($this->get_uid())
      return $this->get_uid();
    $res = db::Query("INSERT INTO main.users DEFAULT VALUES RETURNING id", [], true);
    return $this->login($res->id);
  }

  protected function do_oneclick_reg()
  {
    if ($this->get_uid())
      return
      [
        "reset" => '/'
      ];

    return
    [
      "design" => "auth/store_account",
      "data" => $this->get_forced_uid(),
    ];
  }
}
