<?php
/**
 * Created by PhpStorm.
 * User: nathaly Cedeño
 * Date: 4/10/19
 * Time: 13:10
 */
namespace App;
use function GuzzleHttp\default_ca_bundle;
use Illuminate\Support\Str;
class Cudrequest extends Api
{
    protected $table = 'cudrequest';

    protected $fillable = [];

    protected $hidden = [];

    //was_completed = 0; no procesado, was_completed = 1; procesado.
    protected static function process( $type, $request, $id, $action = null)
    {
        try{         
            $obj = new self;
            $cudreq = false; $act = 1; $default_location = null; $currentObject = null;
            if( ( isset($id) and ( $id !== 0 ) ) || ( $action == 'delete' ) ) {
                $cud['id'] = $id;
                if( self::validUuid($id) )
                    $currentObject = \App\Cudrequest::where([['cudrequest_id', $id], ['was_completed', 0]])->first();
                $cudreq = ( self::validUuid($id) and !empty($currentObject) ) ? true : false;

                $act = ($action == 'delete') ? $act = 3 : $act = 2;

                if( !is_null( $currentObject ) and $type !== 'Company_info' and $type !== 'Security_grant' and $type !== 'Commodities_Features') {
                    $getId = json_decode( $currentObject->request )->id;
                    $request->id = $getId;
                }

                if( $cudreq && $type == 'Commodities_Features' ){
                    $getId = json_decode( $currentObject->request )->commodities_features_id;
                    $request->commodities_features_id = $getId;
                }
            }

            if( $action !== 'delete' and $type !== 'LinkedSellers'  ) {
                if (isset(json_decode($request)->id) && (json_decode($request)->id == 0) ||  ( isset(json_decode($request)->commodities_features_id) && (json_decode($request)->commodities_features_id == 0) ) && \App\Cudrequest::validUuid($id)) {
                    $act = 1;
                }
            }

            if( $action == 'process' ){
                $act = 1;
            }

            $obj->cudrequest_id = $cudreq ? $id : (string)Str::uuid();
            $obj->cudtype_id = $act;
            if( $type == "Tanks" )
                $default_location  = $request->branch_id !== null  ? $request->branch_id : \App\Company_info::pluck('default_location')[0];
            else if( $cudreq and $action == 'delete')
                $default_location = $currentObject['location_id'];
            else
                $default_location = \App\Company_info::pluck('default_location')[0];
            $obj->location_id = $default_location;
            $obj->entity_name = $type;
            $obj->request = json_encode($request, JSON_UNESCAPED_UNICODE);
            $obj->request_date = date('Y-m-d H:i:s');
            $obj->was_completed = 0;
            $obj->date_was_completed = NULL;
            $obj->error_code = 0;

            if ( $cudreq ) {
                if($action == 'delete'){
                    \App\Cudrequest::where('cudrequest_id', $id)->delete();
                    $obj->request = json_encode($cud);
                }else {
                    \App\Cudrequest::where('cudrequest_id', $id)->update(['request' => json_encode($request), 'cudtype_id' => $act, 'was_completed' => 0, 'date_was_completed' => 0, 'error_code' => 0]);
                }
            }else{
                $obj->save();
            }

            self::send_sqs($obj, $cudreq, $action, $default_location);
            return $obj;
        } catch( \Exception $e ){
            return $e->getMessage();
        }
    }

    public static function delrequest( $id, $type, $branch = null ){
        $currentObject = \App\Cudrequest::where([['cudrequest_id', $id], ['was_completed', 0]])->first();
        $cudreq = !empty($currentObject);

        if( $cudreq ) {
            $request = \App\Cudrequest::select('request')->where('cudrequest_id',$id)->first();
            $request = json_decode($request["request"]);
            $request->id = 0;
            $request->password = '';
        }else {

            if($type == 'Tanks'){
                $request = \DB::table(strtolower($type))->where('source_id', $id)->where('branch_id', $branch)->first();
                $request->id = $id;
            }else if($type == 'Commodities_Features'){
                $request = \DB::table(strtolower($type))->where('commodities_features_id', $id)->first();
                $request->id = $id;
            }else {
                $request = \DB::table(strtolower($type))->find($id);
            }
            if($request !== null){
                if( $type == 'Users'){
                    $request->contrasena = $request->password;
                    unset($request->password);
                    $request = $request;
                }else {
                    $request = $request;
                }
            }else{
                return 'ID does not exist';
            }
        }
        $del = \App\Cudrequest::process( $type, $request, $id, $action = 'delete'  );

        return $del;
    }

    protected static function send_sqs( $obj, $cudreq, $action, $default_location)
    {
        try{
            $array = [
                'group' => env('INSTANCE_ID'),
                'type' => 'REQUEST',
                'action' => ( $cudreq and $action == 'delete' ) ? 'DELETECUDREQUEST' : 'CUDREQUEST',
                'destination' => $default_location,
                'message' => json_encode($obj, JSON_FORCE_OBJECT)
            ];

            \App\SQS::send($array, 'local', $default_location);
        }catch (\Exception $e){
            return $e->getMessage();
        }
    }

    public static function cudrequest($entity)
    {
        try {
            $ent = array_values($entity);
            $cud = array();
            $req = \App\Cudrequest::select('cudrequest_id', 'request', 'cudtype_id', 'entity_name', 'error_code')->where('was_completed', 0)->whereIn('entity_name', $ent)->get()->toArray();
            if( !empty($req) ) {
                foreach ($req as $k => $value) {
                    $obj = json_decode($value['request']);
                    if( isset($obj->contrasena)){
                        unset($obj->contrasena);
                    }

                    if (in_array("Security_grant", $entity)) {
                        if ($value["entity_name"] == "Security_grant") {
                            $process = \App\Security_grant::where("user", $obj->user)->pluck('process');
                            $obj->current_process = $process;
                        }
                    }

                    if (in_array("LinkedSellers", $entity)) {
                        if( $value["entity_name"] == "LinkedSellers"){
                            if ($obj->id !== 0) {
                                $obj->last_parent = \App\Sellers::where('id', $obj->id)->pluck('seller_id_parent')[0];
                            }
                        }
                    }

                    if (in_array("Commodities", $entity)) {
                        if ($value['cudtype_id'] == 2 || $value['cudtype_id'] == 3) {
                            $meta = !empty(\App\Metadata_commodities::where('commodity_id', $obj->id)->pluck('icon_name')[0]) ? \App\Metadata_commodities::where('commodity_id', $obj->id)->pluck('icon_name')[0] : "others";
                            $obj->icon_name = $meta;
                        }
                    }

                    $obj->cudrequest_id = $value['cudrequest_id'];
                    $obj->cudtype_id = $value['cudtype_id'];
                    $obj->error_code = $value['error_code'];
                    $obj->entity_name = $value['entity_name'];
                    $cud[] = $obj;
                }
                return $cud;
            }
        }catch (\Exception $e){
            return $e->getMessage();
        }
    }

    public static function validUuid($id){
        try{
            $UUIDv4 = '/^[0-9A-F]{8}-[0-9A-F]{4}-4[0-9A-F]{3}-[89AB][0-9A-F]{3}-[0-9A-F]{12}$/i';
            $cudreq = preg_match($UUIDv4, $id);

            return $cudreq;
        }catch (\Exception $e) {
            return $e->getMessage();
        }
    }
}
