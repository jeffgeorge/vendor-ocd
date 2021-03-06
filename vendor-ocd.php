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

if (php_sapi_name() != "cli"){
  echo "<html><title>Vendor OCD, a Thing for Destiny</title><body style='font-family: sans-serif'><h1>Vendor OCD, a Thing for Destiny</h1><hr />\n";
}

require "lib.php";
require "config.php";

if (!empty($config['cookie_file'])) $cookie_file = $config['cookie_file'];

$config["platform_id"] = ($config["platform"] == "xbox") ? "1" : "2";

do_webauth($config["platform"],$config["platform_user"],$config["platform_pass"]);

// figure out if we've got bungled & bungleatk cookies
$csrf = false;
$atk = false;
$cookies = explode("\n",file_get_contents($cookie_file));
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
}
else {
  die("Character Lookup Error");
}

// some hard-coded stuff, for now.
$vendors = array(
  "levante" => array(
    "id" => "134701236",
    "name" => "Eva Levante - Outfitter",
  ),
  "holiday" => array(
    "id" => "459708109",
    "name" => "Amanda Holiday - Shipwright",
  ),
  "variks" => array(
    "id" => "1998812735",
    "name" => "Variks - House of Judgement",
  ),
  "petra" => array(
    "id" => "1410745145",
    "name" => "Petra Venj - Queen's Wrath",
  )
);

$kiosks = array(
  "emblems" => "3301500998",
  "shaders" => "2420628997",
  "ships" => "2244880194",
  "sparrows" => "44395194",
);

// What categories do we not care about?
$excluded_categories = array(
  "Ornaments",
  "Available Bounties",
  "SRL Gear",
  "Challenge of the Elders Entry",
  "Prison of Elders Bounties",
  "Consumables",
  "Queen's Wrath Bounties",
);

$needed_items = array();

// First, figure out what we own already
// Note: Terrible.
foreach ($kiosks as $kiosk){
  $ch = curl_init();
  curl_setopt_array($ch, $default_options);
  curl_setopt_array($ch, array(
    CURLOPT_URL => BUNGIE_API.$config['platform_id']."/MyAccount/Character/".$char['characterId']."/Vendor/".$kiosk."/Metadata/",
  ));
  
  $kiosk_result = json_decode(curl_exec($ch), TRUE)["Response"]["data"]["vendor"]["saleItemCategories"];
  
  foreach ($kiosk_result as $category){
    if (!in_array($category["categoryTitle"],$excluded_categories)){

      foreach ($category["saleItems"] as $kiosk_item){
        if (isset($kiosk_item["unlockStatuses"][0]["isSet"]) && $kiosk_item["unlockStatuses"][0]["isSet"] == false){
          $needed_items[] = $kiosk_item["item"]["itemHash"];
        };
      }
    }
  }
}

// Now, iterate over Vendors to find out what we need to buy
foreach ($vendors as $vendor){
  $ch = curl_init();
  curl_setopt_array($ch, $default_options);
  curl_setopt_array($ch, array(
    CURLOPT_URL => BUNGIE_API.$config['platform_id']."/MyAccount/Character/".$char['characterId']."/Vendor/".$vendor['id']."/Metadata/",
  ));

  $vendor_result = json_decode(curl_exec($ch), TRUE)["Response"]["data"]["vendor"];

  $categories = $vendor_result["saleItemCategories"];
  curl_close($ch);

  if (php_sapi_name() == "cli"){
    echo "===========================\n";
    echo $vendor['name'] . "\n";
    echo "Next Refresh in ". relativeTime(strtotime($vendor_result["nextRefreshDate"])) . "\n";
    echo "===========================\n";
  }
  else {
    echo "<h2>" . $vendor['name'] . "</h2>\n";
    echo "<small>Next Refresh in ". relativeTime(strtotime($vendor_result["nextRefreshDate"])) . "</small>\n";
  }

  foreach ($categories as $category){
    if (!in_array($category["categoryTitle"],$excluded_categories)){
      if (php_sapi_name() == "cli"){
        echo $category["categoryTitle"] . "\n";
      }
      else {
        echo "<p>" . $category["categoryTitle"] . "</p>\n";
        echo "<ul>\n";
      }

      $got_at_least_one = false;
      foreach ($category["saleItems"] as $vendor_item){
        $ch = curl_init();
        curl_setopt_array($ch, $default_options);
        curl_setopt_array($ch, array(
          CURLOPT_URL => BUNGIE_API."Manifest/6/".$vendor_item["item"]["itemHash"]."/",
        ));
        
        //$buy = (in_array($vendor_item["item"]["itemHash"],$needed_items)) ? "$":" ";
        if (in_array($vendor_item["item"]["itemHash"],$needed_items)) {
          $got_at_least_one = true;
          if (php_sapi_name() == "cli"){
            echo " - " . json_decode(curl_exec($ch), TRUE)["Response"]["data"]["inventoryItem"]["itemName"] . "\n";
          }
          else {
            echo "<li>" . json_decode(curl_exec($ch), TRUE)["Response"]["data"]["inventoryItem"]["itemName"] . "</li>\n";
          }
        }
      }
      
      if (!$got_at_least_one){
        if (php_sapi_name() == "cli"){
          echo " - Got 'em all, come back later\n";
        }
        else {
          echo "<li>Got 'em all, come back later</li>\n";
        }
      }
      
      echo (php_sapi_name() == "cli") ? "\n":"</ul>\n";
    }
  }
  echo (php_sapi_name() == "cli") ? "\n":"<hr />";
}

if (php_sapi_name() != "cli"){
  echo "\n</body>\n";
}