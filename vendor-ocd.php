<?php

define("BUNGIE_API", "https://www.bungie.net/Platform/Destiny/");
define("BUNGIE_URL", "https://www.bungie.net/");

global $cookie_file;
$cookie_file = "cookies.txt";

$config = array(
  "platform"        => "",
  "platform_id"     => "",
  "platform_user"   => "",
  "platform_pass"   => "",
  "username"        => "",
  "apikey"          => ""
);

require "auth.php";
require "config.php";

$config["platform_id"] = ($config["platform"] == "xbox") ? "1" : "2";

do_webauth($config["platform"],$config["platform_user"],$config["platform_pass"]);

// figure out if we've got bungled & bungleatk cookies
$csrf = false;
$atk = false;
$cookies = explode("\n",file_get_contents("cookies.txt"));
foreach ($cookies as $cookie) {
  $pieces = explode("\t", $cookie);
  if (count($pieces) > 1){
    if ($pieces[5] == "bungled"){
      $csrf = $pieces[6];
    }
    if ($pieces[5] == "bungleatk"){
      $atk = true;
    }
  }
}
if ($csrf == false){
  die("No bungled cookie, check your credentials");
}
if ($atk == false){
  die("No bungleatk cookie, check your XBL/PSN credentials");
}

$user_agent = "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.1) Gecko/20061204 Firefox/2.0.0.1";
$default_options = array(
  CURLOPT_USERAGENT => $user_agent,
  CURLOPT_COOKIEJAR => $cookie_file,
  CURLOPT_COOKIEFILE => $cookie_file,
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_SSL_VERIFYHOST => 2,
  CURLOPT_SSL_VERIFYPEER => false,
  CURLOPT_HTTPHEADER => array(
    'X-API-Key: '.$config['apikey'],
    'X-CSRF: '.$csrf,
  ),
);

