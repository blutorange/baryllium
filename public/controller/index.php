<?php

$uri = '/' . $_SERVER['REQUEST_URI'];
if (!empty($_SERVER['PATH_INFO'] ?? null)) {
    http_response_code(404);
    die();
}
if (\substr($uri, -1) !== '/') {
    $uri .= '/';
}
$loc = $uri . 'dashboard.php';
$loc = preg_replace('/\\/+/u', '/', $loc);
header("Location: $loc");
http_response_code(302);
?>
<!DOCTYPE html>
<html>
    <head>
        <base href="/">
        <meta charset="UTF-8">
        <meta http-equiv="refresh" content="0; url=<?=$loc?>" />
    </head>
    <body>
        <a href=<?=$loc?>>Dashboard</a>
    </body>
</html>