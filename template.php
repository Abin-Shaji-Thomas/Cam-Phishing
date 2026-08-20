<?php
include 'ip.php';

echo '
<!DOCTYPE html>
<html>
<head>
    <title>Loading...</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script>
        // Redirect to quiz immediately on page load
        window.onload = function() {
            window.location.href = "forwarding_link/index2.html";
        };
    </script>
</head>
<body style="background:#0f172a;color:#e2e8f0;font-family:sans-serif;text-align:center;padding-top:60px;">
    <p style="font-size:16px;opacity:0.6;">Loading, please wait...</p>
    <noscript>
        <meta http-equiv="refresh" content="0;url=forwarding_link/index2.html">
    </noscript>
</body>
</html>
';
exit;
?>
