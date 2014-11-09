<?php

class vk extends api
{
    private $my_token;

    protected function Reserve()
    {
        return
        [
            "reset" => $this->make_url(),
        ];
    }

    protected function Auth()
    {
        global $_GET;

        $token = $this->get_access_token($_GET['code']);
        phoxy_protected_assert($token, ["error" => "Токен устарел"]);

        // $userInfo = $this->get_user_info($token['user_id'], $token['access_token']);
        $res = $this->save_token($token['user_id'], $token['access_token'], $token['expires_in']);

        if (!isset($res['uid']))
            return
            [
                "error" => "Что то пошло не так."
            ];
        
        //include('api/Users/modify.php');
        //C::modify()->Name($userInfo['first_name'], $userInfo['last_name']);
        //C::modify()->Avatar($userInfo['photo_big']);

        return
        [
            //"reset" => "/",
            "data" => ["uid" => LoadModule('api', 'auth')->login($res['uid'])],
            "design" => "auth/vk/return",
        ];
    }

    private function make_url()
    {
        $url = 'http://oauth.vk.com/authorize';

        //var_dump(phoxy_conf()['ip']);
        //if (phoxy_conf()['ip'] == '213.21.7.6')
           // var_dump(conf()->application->vk->client_id);

        $params =
        [
            'client_id'     => conf()->auth->vk->client_id,
            'redirect_uri'  => conf()->auth->vk->redirect_uri,
            'response_type' => 'code',
            'scope' => 'status,notify,email',
        ];

        return $url.'?'. http_build_query($params);
    }

    public function token()
    {
        if ($this->my_token)
            return $this->my_token;

        $vkid = $this->vkid_by_uid(LoadModule('api', 'auth')->uid());
        if (!$vkid)
            throw new exception("Not registered with vk"); // Todo: redirect and autoauth vk
        return $this->my_token = $this->token_by_vkid($vkid);
    }

    public function get_access_token( $code )
    {        
        $params = 
        [
            'client_id' => conf()->auth->vk->client_id,
            'client_secret' => conf()->auth->vk->client_secret,
            'redirect_uri'  => conf()->auth->vk->redirect_uri,
            'code' => $code,
        ];

        $token = $this->api_request('https://oauth.vk.com/access_token', $params);

        return $token;
    } 

    public function save_token( $id, $token, $expire )
    {
        return db::ConditionalQuery
        (
          function() use ($id)
          {
            return db::Query("SELECT count(*) FROM public.\"users.vk\" WHERE vkid=$1", [$id], true)['count'];
          },
          function () use ($id, $token, $expire)
          {
            return db::Query("UPDATE public.\"users.vk\" SET token=$2, expires=now()+$3::interval WHERE vkid=$1 RETURNING uid",
                [$id, $token, $expire], true);
          },
          function () use ($id, $token, $expire)
          {
            return db::Query("INSERT INTO public.\"users.vk\"(uid, vkid, token, expires) VALUES ($1, $2, $3, now()+$4::interval) RETURNING uid",
                [LoadModule('api', 'auth')->do_oneclick_reg(), $id, $token, $expire], true);
          }
        );
    }

    private function api_request( $method, $params )
    {   // Todo exception on fault
        $api = $method.'?'.http_build_query($params);
        $res = @file_get_contents($api);        
        return json_decode($res, true);
    }

    public function vkid_by_uid( $uid )
    {
        $res = db::Query("SELECT vkid FROM public.\"users.vk\" WHERE uid=$1", [$uid], true);
        return $res['vkid'];
    }

    private function token_by_vkid( $vkid )
    {
        $res = db::Query("SELECT token, (now() < expires) as fresh FROM public.\"users.vk\" WHERE vkid=$1", [$vkid], true);
        if (!$res)
            return false;
        if ($res['fresh'])
            return $res['token'];
        return NULL;
    }

    /*  get_user_info(vk_id): trying to extract his token and call
        get_user_info(vk_id, token): regular call
        get_user_info(vk_id, vk_id): trying to extract second token and call
     */
    public function get_user_info( $user_id, $access_token = NULL )
    {
        if (!$this->detect_vk_id($user_id))
            throw new exception('First param should be vkid');

        if (is_null($access_token))
            $token = $this->token_by_vkid($user_id);
        else if ($this->detect_vk_id($access_token))
            $token = $this->token_by_vkid($access_token);
        else
            $token = $access_token;

        $params =
        [
            'uids'         => $user_id,
            'fields'       => 'uid,first_name,last_name,screen_name,sex,bdate,photo_big',
            'access_token' => $token
        ];

        $user_info = $this->api_request('https://api.vk.com/method/users.get', $params);

        return array_shift($user_info["response"]);
    }

    private function detect_vk_id( $data )
    {
        $try = (int)$data;
        return "$try" == $data;
    }
}
