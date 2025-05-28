<?php

namespace classes;

class Token {
    
    /*
    burada configden tokenı alıyoruz, type ile beraber form mu yoksa login mi mantığı , daha sonra buna 
    benzersiz bir id oluşturuyor hash e çeviriyor en son sessiona atıyor
    */
    public static function generate($type) {
        return Session::put(Config::get("session/tokens/$type"), md5(uniqid()));
    }

    
    /* burada token name i alıyoruz ve böyle bir token name var mı ve sessiondaki ile uyuşuyor mu bakar
    Eğer uyuşuyorsa bu tokenı silerki tekrar kullanılmasın .
    */
    public static function check($token, $type) {
        $tokenName = Config::get("session/tokens/$type");

        if(Session::exists($tokenName) && $token === Session::get($tokenName)) {
            Session::delete($tokenName);
            return true;
        }

        return false;
    }
}