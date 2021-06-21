<?php

require_once __DIR__ . '/../vendor/autoload.php';

require_once __DIR__ . '/../routes/web.php';

use PHPImageBank\App\Router;

if (php_sapi_name() == 'cli-server') {
    $info = parse_url($_SERVER['REQUEST_URI']);
    $php = explode(".", $info["path"]);
    $ext = end($php);
    $exts = ['png', 'jpg', 'jpeg', 'gif', 'css', 'js'];
    if (in_array($ext, $exts)) {
        if (file_exists(__DIR__ . $info["path"])) {
            header("Content-type: image");
            readfile(__DIR__ . $info["path"]);
            exit;
        }
    }
}

session_start();
Router::run();