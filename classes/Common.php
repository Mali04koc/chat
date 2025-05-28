<?php

namespace classes;

class Common {


    //  Bu metot, bir kaynak ($source) içinde belirli bir alanın ($fieldName) var olup olmadığını kontrol eder ve varsa değerini döndürür.
    public static function getInput($source, $fieldName) {
        if(isset($source[$fieldName])) {
            return $source[$fieldName];
        }

        return '';
    }

    
    public static function unique_multidim_array($array, $key) {
        $temp_array = array();
        $i = 0;
        $key_array = array();
    
        foreach($array as $val) {
            if (!in_array($val->getPropertyValue($key), $key_array)) {
                $key_array[$i] = $val->getPropertyValue($key);
                $temp_array[$i] = $val;
            }
            $i++;
        }
        return $temp_array;
        
/* Özellikle bir dizi içinde yinelenen öğeleri filtrelemek için kullanılır. Örneğin, bir dizi nesne içinde belirli bir özellik (property) değerine göre benzersiz hale getirilmek istenirse kullanılır.

İşleyiş:
Girdi Parametreleri:

$array: Filtrelenecek dizi (muhtemelen nesneler içerir).

$key: Benzersizlik kontrolünün yapılacağı özellik (property) adı.

Geçici Diziler:

$temp_array: Benzersiz nesneleri tutmak için kullanılır.

$key_array: Benzersizlik kontrolü için kullanılan bir anahtar dizisidir.

Döngü:

Dizideki her öğeyi kontrol eder (foreach).

getPropertyValue($key) metodunu çağırarak nesnenin belirli bir özelliğini alır.

Bu özellik zaten $key_array içinde değilse:

$key_array dizisine ekler.

$temp_array dizisine bu öğeyi ekler.

Döngü tamamlandığında benzersiz öğelerden oluşan $temp_array döndürülür.*/

    }
}