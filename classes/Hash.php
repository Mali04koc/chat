<?php

namespace classes;

class Hash {
    
    /*Verilen bir string ve isteğe bağlı bir salt değerini birleştirerek SHA-256 algoritması ile bir hash üretir.*/
    public static function make($string, $salt = '') {
        return hash("sha256", $string . $salt);
    }

    // burada random salt değeri üretilir
    public static function salt($length) {
        return bin2hex(random_bytes($length));
    }
    
    /*
        uniqid(): Benzersiz bir ID üretir. Bu, mikro saniye tabanlı bir zaman damgasına dayanır.

        self::make(uniqid()): Üretilen ID'yi make() fonksiyonu yani hash fonksiyonumuzu kullanarak hashler.  
        
        $uniqueHash = Hash::unique(); //örnek  benzersiz bir hash üretir

    */
    public static function unique() {
        return self::make(uniqid());
    }
    
}