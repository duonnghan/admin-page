<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Document</title>
</head>

<body>
    <?php

    $thisFile = str_replace('\\', '/', __FILE__);
    $docRoot = $_SERVER['DOCUMENT_ROOT'];

    $webRoot  = str_replace(array($docRoot, 'library/config.php'), '', $thisFile);
    $srvRoot  = str_replace('library/config.php', '', $thisFile);

    define('WEB_ROOT', $webRoot);
    define('SRV_ROOT', $srvRoot);

    echo WEB_ROOT;
    echo SRV_ROOT;
    ?>
</body>

</html>
