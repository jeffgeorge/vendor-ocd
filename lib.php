<?php
// do_webauth, a helpful Bungie <-> PSN and Bungie <-> XBL authentication Thing
// Credit: lowlines & destinydevs - http://destinydevs.github.io/BungieNetPlatform/docs/Authentication

function do_webauth($method, $username, $password) {
  global $cookie_file; // a valid file path to where the cookies will be stored (ie cookie.txt)
  $user_agent = "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.1) Gecko/20061204 Firefox/2.0.0.1";

  $methods = array(
    'psn' => 'Psnid',
    'xbox' => 'Xuid'//'Wlid'
  );
  $dest = 'Wlid'; if (isset($methods[$method])) $dest = $methods[$method];
  $url = BUNGIE_URL.'/en/User/SignIn/'.$dest;

  $default_options = array(
    CURLOPT_USERAGENT => $user_agent,
    CURLOPT_COOKIEJAR => $cookie_file,
    CURLOPT_COOKIEFILE => $cookie_file,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_SSL_VERIFYHOST => 2,
  );

  // Get Third Party Authorization URL
  $ch = curl_init();
  curl_setopt_array($ch, $default_options);
  curl_setopt_array($ch, array(
    CURLOPT_URL => $url,
  ));
  curl_exec($ch);
  $redirect_url = curl_getinfo($ch)['redirect_url'];
  curl_close($ch);

  // Bungie Cookies are still valid
  if (!$redirect_url) return true;

  // Try to authenticate with Third Party
  $ch = curl_init();
  curl_setopt_array($ch, $default_options);
  curl_setopt_array($ch, array(
    CURLOPT_URL => $redirect_url,
  ));
  $auth_result = curl_exec($ch);
  $auth_info = curl_getinfo($ch);
  $auth_url = $auth_info['redirect_url'];

  // Normally authentication will produce a 302 Redirect, but Xbox is special...
  if ($auth_info['http_code'] == 200) $auth_url = $auth_info['url'];

  curl_close($ch);

  // No valid cookies
  if (strpos($auth_url, $url.'?code') !== 0) {
    $result = false;
    switch($method) {
      case 'psn':
        $login_url = 'https://auth.api.sonyentertainmentnetwork.com/login.do';

        // Login to PSN
        $ch = curl_init();
        curl_setopt_array($ch, $default_options);
        curl_setopt_array($ch, array(
          CURLOPT_URL => $login_url,
          CURLOPT_POST => 3,
          CURLOPT_POSTFIELDS => http_build_query(array(
            'params' => 'cmVxdWVzdF9sb2NhbGU9ZW5fVVMmcmVxdWVzdF90aGVtZT1saXF1aWQ=', // without empty server result
            'j_username' => $username,
            'j_password' => $password,
            'rememberSignIn' => 1 // Remember signin
          )),
        ));
        curl_exec($ch);
        $redirect_url = curl_getinfo($ch)['redirect_url'];
        curl_close($ch);

        if (strpos($redirect_url, 'authentication_error') !== false) return false;

        // Authenticate with Bungie
        $ch = curl_init();
        curl_setopt_array($ch, $default_options);
        curl_setopt_array($ch, array(
          CURLOPT_URL => $redirect_url,
          CURLOPT_FOLLOWLOCATION => true
        ));
        curl_exec($ch);
        $result = curl_getinfo($ch);
        curl_close($ch);
        break;
      case 'xbox':
        $login_url = 'https://login.live.com/ppsecure/post.srf?'.substr($redirect_url, strpos($redirect_url, '?')+1);
        preg_match('/id\="i0327" value\="(.*?)"\//', $auth_result, $ppft);

        if (count($ppft) == 2) {
          $ch = curl_init();
          curl_setopt_array($ch, $default_options);
          curl_setopt_array($ch, array(
            CURLOPT_URL => $login_url,
            CURLOPT_POST => 3,
            CURLOPT_POSTFIELDS => http_build_query(array(
              'login' => $username,
              'passwd' => $password,
              'KMSI' => 1, // Stay signed in
              'PPFT' => $ppft[1]
            )),
          ));
          $auth_result = curl_exec($ch);
          $auth_url = curl_getinfo($ch)['url'];
          curl_close($ch);

          $ch = curl_init();
          curl_setopt_array($ch, $default_options);
          curl_setopt_array($ch, array(
            CURLOPT_URL => $redirect_url,
            CURLOPT_FOLLOWLOCATION => true
          ));
          curl_exec($ch);
          $result = curl_getinfo($ch);
          curl_close($ch);
        }
        break;
    }
    $result_url = $result['url'];
    if ($result['http_code'] == 302) $result_url = $result['redirect_url'];

    // Account has not been registered with Bungie
    if (strpos($result_url, '/Register') !== false) return false;

    // Login successful, "bungleatk" should be set
    // Facebook/PSN should return with ?code=
    // Xbox should have ?wa=wsignin1.0
    return strpos($result_url, $url) === 0;
  }
  // Valid Third Party Cookies, re-authenticating Bungie Login
  $ch = curl_init();
  curl_setopt_array($ch, $default_options);
  curl_setopt_array($ch, array(
    CURLOPT_URL => $auth_url,
  ));
  curl_exec($ch);
  curl_close($ch);
  return true;
}

function relativeTime($time) {

  $d[0] = array(1,"second");
  $d[1] = array(60,"minute");
  $d[2] = array(3600,"hour");
  $d[3] = array(86400,"day");
  $d[4] = array(604800,"week");
  $d[5] = array(2592000,"month");
  $d[6] = array(31104000,"year");

  $w = array();

  $return = "";
  $now = time();
  $diff = ($now-$time);
  $secondsLeft = $diff;

  for($i=6;$i>-1;$i--) {
    $w[$i] = intval($secondsLeft/$d[$i][0]);
    $secondsLeft -= ($w[$i]*$d[$i][0]);
    if($w[$i]!=0)
    {
      $return.= abs($w[$i]) . " " . $d[$i][1] . (($w[$i]>1)?'':'s') ." ";
    }
  }

  return $return;
}