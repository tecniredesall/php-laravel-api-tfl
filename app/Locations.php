<?php

namespace App;

class Locations extends Api
{

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'locations';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];

    public static function mostrar($id = null, $request = array())
    {
        $query = \App\Locations::select('*')->where( 'status', '<>', 5 )->get()->toArray();

        return response()->json($query);
    }

    private static function del($id)
    {
        \DB::beginTransaction();
        try {
            $obj = self::find($id);
            $obj->status = 5;

            $json = new \stdClass();
            $json->idInstance = env('INSTANCE_ID');
            $json->idLocation = $id;
            $json->name = $obj->name;
            $json->latitude = isset( $obj->latitude ) ? $obj->latitude : 0;
            $json->longitude = isset( $obj->longitude ) ? $obj->longitude : 0;

            $object = new \stdClass();
            $object->event = 'location.delete';
            $object->payload = $json;

            $array = [
                'type' => 'REQUEST',
                'action' => 'RequestSiloSysHarvexTicket',
                'destination' => $id,
                'message' => json_encode( $object )
            ];

            \App\SQS::send( $array, 'local', null, true );


            $obj->push();
            \DB::commit();
            return self::mostrar(null, null);
        } catch (Exception $e) {
            \DB::rollBack();
            return false;
        }
    }

    private static function saveOrUpdate($request, $id)
    {
        \DB::beginTransaction();
        try {
            if( $id !== 0)
                $obj = self::find($id);
            else
                $obj = new self;
            $obj->status = $request->status;
            $obj->name = $request->name;
            $obj->local_host_ip =  isset( $request->local_host_ip ) ? $request->local_host_ip : NULL;
            $obj->remote_host_ip =  isset( $request->remote_host_ip ) ? $request->remote_host_ip : NULL;
            $obj->database_name =  isset( $request->database_name ) ? $request->database_name : NULL;
            $obj->username =  isset( $request->username ) ? $request->username : NULL;
            $obj->password =  isset( $request->password ) ? $request->password : NULL;
            $obj->rport =  isset( $request->rport ) ? $request->rport : NULL;
            $obj->aws_host =  isset( $request->aws_host ) ? $request->aws_host : NULL;
            $obj->pdfpath =  isset( $request->pdfpath ) ? $request->pdfpath : NULL;
            $obj->slave_username =  isset( $request->slave_username ) ? $request->slave_username : NULL;
            $obj->slave_password =  isset( $request->slave_password ) ? $request->slave_password : NULL;
            $obj->slave_port =  isset( $request->slave_port ) ? $request->slave_port : NULL;
            $obj->sqs_key =  isset( $request->sqs_key ) ? $request->sqs_key : NULL;
            $obj->sqs_secret =  isset( $request->sqs_secret ) ? $request->sqs_secret : NULL;
            $obj->sqs_name =  isset( $request->sqs_name ) ? $request->sqs_name : NULL;
            $obj->sqs_url =  isset( $request->sqs_url ) ? $request->sqs_url : NULL;
            $obj->sqs_arn =  isset( $request->sqs_arn ) ? $request->sqs_arn : NULL;
            $obj->latitude =  isset( $request->latitude ) ? $request->latitude : 0;
            $obj->longitude =  isset( $request->longitude ) ? $request->longitude : 0;
            if( $id == 0)
                $obj->push();

            $json = new \stdClass();
            $json->idInstance = env('INSTANCE_ID');
            if( $id !== 0) {
                $json->idLocation = $id;
                $data = self::find($id);
                if( ( $data["name"] !== strval($request->name) ) || ( $data["latitude"] !== floatval($request->latitude) ) || ( $data["longitude"] !== floatval($request->longitude) ) )
                {
                    $json->name = $request->name;
                    $json->latitude = $request->latitude;
                    $json->longitude = $request->longitude;

                    $object = new \stdClass();
                    $object->event = 'location.update';
                    $object->payload = $json;
                    self::sqsHrvxTicket($object);
                }
                $obj->save();
            }else{
                $json->idLocation = $obj->id;
                $json->name = $request->name;
                $json->latitude = isset( $request->latitude ) ? $request->latitude : 0;
                $json->longitude = isset( $request->longitude ) ? $request->longitude : 0;

                $object = new \stdClass();
                $object->event =  'location.new';
                $object->payload = $json;
                self::sqsHrvxTicket($object);
            }

            \DB::commit();
            return self::mostrar(null, $request);
        } catch (Exception $e) {
            \DB::rollBack();
            return false;
        }
    }

    private static function sqsHrvxTicket($object){
        $array = [
            'type' => 'REQUEST',
            'action' => 'RequestSiloSysHarvexTicket',
            'destination' => env('INSTANCE_ID'),
            'message' => json_encode( $object )
        ];
        \App\SQS::send( $array, 'local', null, true );
    }

    protected static function guardar( $request ){ return static::saveOrUpdate( $request, $id = 0 ); }
    protected static function eliminar($id) { return static::del($id); }
    protected static function actualizar($request, $id) { return static::saveOrUpdate($request, $id); }
}