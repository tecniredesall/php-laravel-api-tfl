<?php


namespace App\Helpers;


class EnvironmentHelper
{
    public static function getAssetPath($path){
        return  self::isLocal() ? asset($path) : secure_asset($path);
    }

    private static function isLocal(){
        return env('APP_ENV') === 'local';
    }
}