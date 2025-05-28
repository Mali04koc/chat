<?php

namespace classes;

class Redirect {
    // yönlendirme başlangıçta null diye verilmiş
    public static function to($location=null) {
        // eğer yönlendirme varsa girer
        if(isset($location)) {
            // eğer yönlendirme sayıysa 404.php sayfama gider
            if(is_numeric($location)) {
                switch($location) {
                    case 404:
                        header("HTTP/1.0 404 Not Found");
                        header("Location: " . Config::get("root/path") . "page_parts/errors/404.php");
                        exit();
                    break;
                }
            }
            // eğer normal bir url ise oraya gider
            header("Location: " . $location);
            exit;
        }
    }
}