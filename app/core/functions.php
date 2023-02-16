<?php

function show($param) {

    echo "<pre>";
    print_r($param);    
    echo "</pre>";

}

function esc($str) {
    return htmlspecialchars($str);
}

function redirect($path) {
    header("Location: " . ROOT . "/" . $path);
    die;
}

function dd($param) {
    echo "<pre>";
    var_dump($param);
    echo "</pre>";

    die();
}

function abort($code = 404) {
    http_response_code($code);

    require ROOT."/app/views/{$code}.php";

    die();
}