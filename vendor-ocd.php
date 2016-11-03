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

// Get user data
$ch = curl_init();
curl_setopt_array($ch, $default_options);
curl_setopt_array($ch, array(
  CURLOPT_URL => BUNGIE_API."SearchDestinyPlayer/".$config['platform_id']."/".$config['username']."/",
));
$user_result = json_decode(curl_exec($ch), TRUE);
$user_info = curl_getinfo($ch);
curl_close($ch);

if ($user_result['ErrorCode'] == 1){
  $user = $user_result['Response'][0];
}
else {
  die("User Lookup Error");
}

// Get character details
$ch = curl_init();
curl_setopt_array($ch, $default_options);
curl_setopt_array($ch, array(
  CURLOPT_URL => BUNGIE_API."/".$config['platform_id']."/Account/".$user['membershipId']."/",
));
$char_result = json_decode(curl_exec($ch), TRUE);
curl_close($ch);

if ($char_result['ErrorCode'] == 1){
  $char = $char_result['Response']['data']['characters'][0]['characterBase'];
  //var_dump($char['characterId']);
}
else {
  die("Character Lookup Error");
}

// some hard-coded stuff, for now.
$vendors = array(
  "levante" => "134701236",
  "holiday" => "459708109"
);

$kiosks = array(
  "emblems" => "3301500998",
  "shaders" => "2420628997",
  "ships" => "2244880194",
  "sparrows" => "44395194",
);

// Lookup Holiday
$ch = curl_init();
curl_setopt_array($ch, $default_options);
curl_setopt_array($ch, array(
  CURLOPT_URL => BUNGIE_API.$config['platform_id']."/MyAccount/Character/".$char['characterId']."/Vendor/".$vendors['holiday']."/",
));
$vendor_result = json_decode(curl_exec($ch), TRUE)["Response"]["data"]["saleItemCategories"][0]["saleItems"];
curl_close($ch);

echo "Amanda Holiday - Shipwright\n";

foreach ($vendor_result as $vendor_item){
  $ch = curl_init();
  curl_setopt_array($ch, $default_options);
  curl_setopt_array($ch, array(
    CURLOPT_URL => BUNGIE_API."Manifest/6/".$vendor_item["item"]["itemHash"]."/",
  ));
  echo " [ ] " . json_decode(curl_exec($ch), TRUE)["Response"]["data"]["inventoryItem"]["itemName"] . "\n";
}
