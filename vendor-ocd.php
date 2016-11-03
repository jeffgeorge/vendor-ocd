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
