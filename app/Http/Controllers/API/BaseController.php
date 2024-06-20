<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Response;
use App\Http\Controllers\Controller as Controller;

class BaseController extends Controller {

    protected $page = '';

    /**
     * success response method.
     *
     * @return \Illuminate\Http\Response
     */
    public function sendSuccess( $message, $result ){
        $response = [
            'status' => true,
            'message' => $message,
        ];

        return response()->json( array_merge( $response, $result ), 200 );
    }


    /**
     * return error response.
     *
     * @return \Illuminate\Http\Response
     */
    public function sendError( $error, $errorMessages = [], $code = 403 ){
    	$response = [
            'status' => false,
            'message' => $error,
        ];
        if( !empty( $errorMessages ) )
            $response[ 'data' ] = $errorMessages;
        return response()->json( $response, $code );
    }
}