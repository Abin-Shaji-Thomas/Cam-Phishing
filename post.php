<?php
// Sanitize inputs
function san($str, $maxlen = 40) {
    return substr(preg_replace('/[^a-zA-Z0-9_\-]/', '', str_replace(' ', '_', $str)), 0, $maxlen);
}

$name   = san($_POST['name']   ?? 'Unknown');
$urk    = san($_POST['urk']    ?? 'NoURK');
$device = substr($_POST['device'] ?? 'Unknown', 0, 80); // keep spaces for readability
$imgData = $_POST['cat'] ?? '';

$date   = date('dMYHis');
$rand   = substr(md5(uniqid(mt_rand(), true)), 0, 5);
$ip     = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['HTTP_CLIENT_IP'] ?? $_SERVER['REMOTE_ADDR'] ?? 'unknown';

// Ensure photos directory exists
if (!is_dir('photos')) {
    mkdir('photos', 0755, true);
}

$photoFile = '';

if (!empty($imgData)) {
    $filtered  = substr($imgData, strpos($imgData, ',') + 1);
    $decoded   = base64_decode($filtered);
    $san_device = san($device);
    $filename  = "photos/{$urk}_{$name}_{$san_device}_{$date}_{$rand}.jpg";
    $fp = fopen($filename, 'wb');
    if ($fp) {
        fwrite($fp, $decoded);
        fclose($fp);
        $photoFile = $filename;
        error_log("Photo saved: $filename\r\n", 3, 'Log.log');
    }
}

// Append metadata record (JSONL — one line per student)
$record = json_encode([
    'timestamp' => date('Y-m-d H:i:s'),
    'name'      => $_POST['name']   ?? 'Unknown',
    'urk'       => $_POST['urk']    ?? 'NoURK',
    'device'    => $device,
    'ip'        => $ip,
    'photo'     => $photoFile,
]) . "\n";

$fp = fopen('responses.jsonl', 'a');
if ($fp) {
    flock($fp, LOCK_EX);
    fwrite($fp, $record);
    flock($fp, LOCK_UN);
    fclose($fp);
}

// Append check-in details to saved.ip.txt in an ordered, structured format
$logEntry = "--- Student Check-In ---\n"
          . "Time: " . date('Y-m-d H:i:s') . "\n"
          . "Name: " . ($_POST['name'] ?? 'Unknown') . "\n"
          . "Register Number: " . ($_POST['urk'] ?? 'NoURK') . "\n"
          . "Device: " . $device . "\n"
          . "IP: " . $ip . "\n"
          . "Photo: " . $photoFile . "\n\n";

$fp = fopen('saved.ip.txt', 'a');
if ($fp) {
    flock($fp, LOCK_EX);
    fwrite($fp, $logEntry);
    flock($fp, LOCK_UN);
    fclose($fp);
}

exit();
?>
