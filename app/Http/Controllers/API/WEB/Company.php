<?php
namespace App\Http\Controllers\API\WEB;

use Illuminate\Http\Request;
use App\Http\Requests;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Company_info;
use App\Http\Controllers\API\BaseController as BaseController;
use Illuminate\Support\Facades\Log;

class Company extends BaseController
{

    public function index(Request $request)
    {
        //
    }

    public function get_company_info( )
    {
        try {
            $company_info = Company_info::select('*')->first();
            $req = \App\Cudrequest::select('cudrequest_id', 'entity_name', 'request', 'error_code')->where('was_completed', 0)->where('entity_name', 'Company_info')->orderBy('request_date', 'desc')->first();
            if( !is_null($req) ) {
                $obj = json_decode($req['request']);
                $obj->cudrequest_id = $req['cudrequest_id'];
                $obj->error_code = $req['error_code'];
                $obj->entity_name = $req['entity_name'];
                $cud[] = $obj;
            }else {
                $cud = [];
            }
            return $this->sendSuccess( '', array( 'data' => $company_info, 'pending' => $cud) );

        } catch (Exception $e) {
            Log::error($e->getMessage());
            return $this->sendError( 'Internal Server Error', array(), 500 );
        }
    }


    public function company_info(Request $request){
        $rules = array(
            'name' => 'required', 'metric_system_id' => 'required',
            'email' => 'email', 'default_location' => 'required'
        );
        $messages = array (
            'name.required' => 'Field required', 'metric_system_id.required' => 'Field required',
            'email.email' => 'This email is invalid', 'default_location.required' => 'Field required'
        );
        $validator = \Validator::make($request->all(), $rules, $messages);
        try{
            if( $validator->fails() ) {
                return $this->sendError('Error', $validator->errors(), 400);
            }else {
                return $this->sendSuccess('Commodity has been created successfully', array('data' => Company_info::guardar($request)));
            }

        } catch( Exception $e ){
            Log::error($e->getMessage());
            return $this->sendError( 'Internal Server Error', array(), 500 );
        }
    }

}
