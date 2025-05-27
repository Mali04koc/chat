<?php
   /*Buradaki amacımız config classı oluşturup içine get fonksiyonu yazmak bu sayede
   $GLOBALS["config"]["mysql"]["host"] host değerini böyle bulmak yerine
   Config::get("mysql/host") şeklinde config classındaki get fonksiyonunu çağırarak bulabiliriz.
   Bu get mantığını init.phpde kullanıyoruz.*/
    
    namespace classes;

    class Config {

        public static function get($path = null) {
         
            
            if($path) {
                $config = $GLOBALS["config"];
                $path = explode('/', $path);

                foreach($path as $bit) {
                    if(isset($config[$bit])) {
                        $config = $config[$bit];
                    }
                }

                return $config;
            }

            /* if ile önce path var mı diye bakıyoruz, sonra değişkenleri atıyoruz ve path değişkenini
            '/' ile ayırıyoruz.Mesela path=mysql/host olsun bunu mysql ve host olarak böldük.
            Sonra foreach ile path değişkenini tek tek alıp config dizisinde var mı diye kontrol ediyoruz.
            Mysql confing içinde var yeni confing değişkenimiz mysql dizisi oldu sonra host mysql(yeni confingimiz)
            içinde var mı ? Evet var o zaman yeni configimiz host oldu.Bu var mı kontrolünü isset fonk yapar
            Sonra return ile hostu döndürüyoruz.*/

            
            return false;
        }
    }

?>