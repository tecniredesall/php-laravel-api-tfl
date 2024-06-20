<?php

namespace App;

use App\SQS\SQSReader;
use App\SQS\SQSSender;
use App\SQS\Clients\SQSClientFactory;

class SQS extends Api
{
    /**
     * @param array $array
     * @param $clientType
     * @param $default_location
     * @param null $hrvx
     * @return \Illuminate\Http\JsonResponse
     */
    public static function send($array = array(), $clientType, $default_location, $hrvx = null)
    {
        if ($default_location === null) {
            $default_location = !is_null(Company_info::pluck('default_location')[0])
                ? Company_info::pluck('default_location')[0]
                : Locations::pluck('id')[0];
        }

        $sender = new SQSSender();
        $clientType = self::NormalizeClient($hrvx, $clientType);

        return $sender->enviar($array, $clientType, $default_location);
    }

    /**
     * @param $who String Indica la procedencia del queue de lectura.
     * Posibles valores: 'local' o 'remote'
     * @throws \Exception
     */
    public static function receive($type)
    {
        $reader = new SQSReader(new SQSClientFactory());
        $reader->read($type);
    }

    /**
     * @param $hrvx
     * @param $who
     * @return mixed|string
     */
    private static function NormalizeClient($hrvx, $who)
    {
        if ($hrvx) {
            $who = 'harvx';
        } elseif ($who == 'local') {
            $who = 'desktop';
        } elseif ($who == 'remote') {
            $who = 'cas';
        }
        return $who;
    }
}
