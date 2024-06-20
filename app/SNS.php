<?php

namespace App;

use Illuminate\Http\Response;

class SNS extends Api {

	/**
     * connection
     *
     * @return string
     */
    private static function connection(){
        $connection = \AWS::createClient( 'sns' );
        try{
            return $connection;
        } catch( \AwsException $e ){
            return error_log( $e->getMessage() );
        }
    }

    /**
     * send
     *
     * @var content
     * @return json
     */
    private static function enviar( $message, $targetArn ){
        self::enviarAndroid();
        $client = static::connection();
        try{
            try{
                $params = [
                    'TargetArn' => $targetArn,
                    'Message' => json_encode([
                        'APNS_SANDBOX' => json_encode([
                            'aps' => [
                                'alert' => $message,
                                'sound' => '1'
                            ]
                        ], JSON_FORCE_OBJECT | JSON_UNESCAPED_UNICODE )
                    ]),
                    'MessageStructure' => 'json'
                ];
                $result = $client->publish( $params );
                return response()->json([ 'status' => true, 'title' => 'Ok', 'msg' => 'Send notification has been successfully', 'data' => $result ], 200 );
            } catch( Exception $a ){
                return response()->json([ 'status' => false, 'title' => 'Err', 'msg' => $a->getMessage() ], 500 );
            }
        } catch( AWSBadRequest $e ){
            return response()->json([ 'status' => false, 'title' => 'Err', 'msg' => $e->getMessage() ], 400 );
        }
    }
    /**
     * send for android
     *
     * @var content
     * @return json
     */
    private static function enviarAndroid( $message, $targetArn ){
        $client = static::connection();
        try{
            try{
                $params = [
                    'TargetArn' => $targetArn,
                    'Message' => json_encode([
                        'GCM' => json_encode([
                            'data' => [
                                'message' => $message
                            ]
                        ], JSON_FORCE_OBJECT | JSON_UNESCAPED_UNICODE )
                    ]),
                    'MessageStructure' => 'json'
                ];
                $result = $client->publish( $params );
                return response()->json([ 'status' => true, 'title' => 'Ok', 'msg' => 'Send notification has been successfully', 'data' => $result ], 200 );
            } catch( Exception $a ){
                return response()->json([ 'status' => false, 'title' => 'Err', 'msg' => $a->getMessage() ], 500 );
            }
        } catch( AWSBadRequest $e ){
            return response()->json([ 'status' => false, 'title' => 'Err', 'msg' => $e->getMessage() ], 400 );
        }
    }
    /**
     * delete
     *
     * @var id
     * @return json
     */
    private static function eliminar( $id ){
        $connect = static::connection();
        try{
            $client = $connect->deleteEndpoint([
                'EndpointArn' => $id
            ]);
            // return true;
            return response()->json([ 'status' => true, 'title' => 'Ok', 'msg' => 'Push has been deleted successfully' ], 200 );
        } catch( AwsException $e ){
            // return false;
            return response()->json([ 'status' => false, 'title' => 'Err', 'msg' => $e->getMessage() ], 500 );
        }
    }

	/**
     * @var array
     * @return json
     */
    protected static function send( $message = "", $targetArn ){ return static::enviar( $message, $targetArn ); }

    /**
     * @var id
     * @return array
     */
    protected static function del( $targetArn ){ return static::eliminar( $targetArn ); }

    /**
     * @var request
     * @return bool
     */
    protected static function addIfNotExistDevice( $request ){
        $a = \App\Metadata_users::firstOrCreate([ 'user_id' => $request->user()->id, 'serial_number' => $request->device_id ]);
        
        $connection = static::connection();
        $snsClient = $connection->createPlatformEndpoint([
            'PlatformApplicationArn' => env( 'AWS_SNS_ARN' ),
            'Token' => $a->serial_number
        ]);
        $b = \App\Metadata_users::find( $a->id );
        $b->target_arn = $snsClient[ 'EndpointArn' ];
        $b->push();
        return true;
    }
}