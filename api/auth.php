<?php

class auth extends api
{
  protected function uid()
  {
    phoxy_protected_assert
    (
      $this->is_user_authorized(),
      [
        "design" => "auth/index"
      ]
    );
    return $this->get_uid();
  }

  public  function get_uid($id = null)
  {
    if (session_status() !== PHP_SESSION_ACTIVE)
      session_start();
    global $_SESSION;

    if (!is_null($id))
      $_SESSION['uid'] = $id;
    if (isset($_SESSION['uid']))
      return $_SESSION['uid'];
    return 0;
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
    return $this->get_uid($id);
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
    $res = db::Query("INSERT INTO public.users DEFAULT VALUES RETURNING id", [], true);
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
