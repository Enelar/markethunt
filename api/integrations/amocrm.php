<?php

/*
private:
  integrations:
    amocrm:
      domain:
      login:
      hash:
*/
class amocrm extends api
{
  private static $cookie = null;

  private function RawQuery($method, $params = [], $add_creditals = false)
  {
    $amocrm_conf = conf()->integrations->amocrm;
    $domain = $amocrm_conf->domain;
    $link = 'https://'.$domain.'.amocrm.ru/private/api/'.$method;

    if (is_null($cookie))
      $cookie = tempnam();
    if ($add_creditals)
    {
      $params['USER_LOGIN'] = $amocrm_conf->login;
      $params['USER_HASH'] = $amocrm_conf->hash;
    }

    $curl = curl_init();

    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_USERAGENT, 'amoCRM-API-client/1.0');
    curl_setopt($curl, CURLOPT_URL, $link);
    curl_setopt($curl, CURLOPT_CUSTOMREQUEST,'POST');
    curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($user));
    curl_setopt($curl, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($curl, CURLOPT_HEADER, false);
    curl_setopt($curl, CURLOPT_COOKIEFILE, $cookie);
    curl_setopt($curl, CURLOPT_COOKIEJAR, $cookie);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);

    $out = curl_exec($curl);
    $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);

    phoxy_protected_assert($code == 200 || $code == 204, "amocrm api failure");

    return $out;
  }

  protected function Auth()
  {
    return $this->RawQuery('auth.php?type=json', [], true);
  }

}