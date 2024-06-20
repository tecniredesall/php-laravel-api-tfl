<?php

namespace App;

use Illuminate\Support\Facades\DB;
use GuzzleHttp\Client;
use mysql_xdevapi\Exception;

class CommoditiesGeneral extends Api
{
    protected $table = 'commodities_general';
    protected $hidden = [];

    public static function getCommoditiesGeneral($page, $size, $lang)
    {
        try {
            $url = env('API_HRVX_LOGIN');
            $client = new \GuzzleHttp\Client(['verify' => false]);
            $headers = [
                'cache-control' => 'no-cache',
                'content-type' => 'application/x-www-form-urlencoded',
            ];
            // build request for get token
            $request = $client->post($url, [
                'headers' => $headers,
                'timeout' => 30,
                'connect_timeout' => true,
                'http_errors' => true,
                'form_params' => [
                    'password' => env('HRVX_PSW'),
                    'audience' => env('HRVX_AUDIENCE'),
                    'grant_type' => env('HRVX_GRANT_TYPE'),
                    'realm' => env('HRVX_REALM'),
                    'username' => env('HRVX_USER_NAME'),
                    'client_id' => env('HRVX_CLIEN_ID')
                ],
            ]);
            // get body response
            $response = $request ? $request->getBody()->getContents() : null;
            $status = $request ? $request->getStatusCode() : 500;
            // check if response is ok
            if ($response && $status === 200) {
                $res = json_decode($response);
                // get token prop
                $token = $res->id_token;
                // build uri
                $completeUrl = env('HRVX_API_COMMODITIES_GENERAL') . "?page" . $page . '&size=' . $size . '&language=' . $lang;
                // get general commodities
                $requestCmo = $client->get($completeUrl, [
                    'headers' => [
                        'authorization' => "bearer " . $token,
                        'cache-control' => 'no-cache',
                        'content-type' => 'application/x-www-form-urlencoded',
                    ],
                    'timeout' => 30,
                    'connect_timeout' => true,
                    'http_errors' => true,
                ]);
                $responseCmo = $requestCmo ? $requestCmo->getBody()->getContents() : null;
                return (object)json_decode($responseCmo);
            }
            return (object)json_decode($response);
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    private static function getAccessData($client)
    {
        $url = env('API_HRVX_LOGIN');
        $headers = [
            'cache-control' => 'no-cache',
            'content-type' => 'application/x-www-form-urlencoded',
        ];
        // build request for get token
        $request = $client->post($url, [
            'headers' => $headers,
            'timeout' => 30,
            'connect_timeout' => true,
            'http_errors' => true,
            'form_params' => [
                'password' => env('HRVX_PSW'),
                'audience' => env('HRVX_AUDIENCE'),
                'grant_type' => env('HRVX_GRANT_TYPE'),
                'realm' => env('HRVX_REALM'),
                'username' => env('HRVX_USER_NAME'),
                'client_id' => env('HRVX_CLIEN_ID')
            ],
        ]);
        return $request;
    }

    public static function updateCommoditiesGeneral($lang)
    {
        try {
            $client = new \GuzzleHttp\Client(['verify' => false]);
            // build request for get token
            $request = static::getAccessData($client);
            // get body response
            $response = $request ? $request->getBody()->getContents() : null;
            $status = $request ? $request->getStatusCode() : 500;
            // check if response is ok
            if ($response && $status === 200) {
                $res = json_decode($response);
                // get token prop
                $token = $res->id_token;
                // build uri
                $page = 1;
                $completeUrl = env('HRVX_API_COMMODITIES_GENERAL') . "?page" . $page . '&size=' . 50 . '&language=' . $lang;
                $responseCmo = static ::getCommoditiesData($completeUrl, $client, $token);

                // get general commodities
                // while ($completeUrl) {
                 //   $completeUrl =
                // }
                dd($responseCmo->metas);
                dd('luisid');
                return true;
            }
            return false;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    private static  function getCommoditiesData($url, $client, $token) {
        // get general commodities
        $requestCmo = $client->get($url, [
            'headers' => [
                'authorization' => "bearer " . $token,
                'cache-control' => 'no-cache',
                'content-type' => 'application/x-www-form-urlencoded',
            ],
            'timeout' => 30,
            'connect_timeout' => true,
            'http_errors' => true,
        ]);
        $responseCmo = $requestCmo ? $requestCmo->getBody()->getContents() : null;
        return (object)json_decode($responseCmo);
    }
}
