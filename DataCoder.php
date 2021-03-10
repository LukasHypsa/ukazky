<?php

namespace App\Drivers;

use Nette\Utils\Json;


class DataCoder
{
    /*
     JSON v databázi:
     {
        "cs": "somethingInCzech",
        "en": "somethingInEnglish"
     }

    I/O na venek komunikuje přes hash pole:
    {
        "cs" => "blaInCs",
        "en" => "blaInEn"
    }
     */

    public function isJSON($string){
        return is_string($string) && is_array(json_decode($string, true)) && (json_last_error() == JSON_ERROR_NONE) ? true : false;
    }

    public function convertDatabaseValueToArray($json) {
        $array = [];

        if($this->isJSON($json))
        {
            try {
                $array = Json::decode($json,Json::FORCE_ARRAY);
            }
            catch (Nette\Utils\JsonException $e) {
                return $array;
            }
        }

        return $array;
    }

    public function convertArrayToDatabaseValue($array) {
        $json = '';

        if(is_array($array))
        {
            try {
                $json = Json::encode($array);
                return $json;
            }

            catch (Nette\Utils\JsonException $e) {
                return $json;
            }
        }

        return $json;
    }
}