<?php

$loc = '/' . $_SERVER['REQUEST_URI'] . '/controller/dashboard.php';
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