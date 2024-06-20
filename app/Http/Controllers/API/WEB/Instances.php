<?php

namespace App\Http\Controllers\API\WEB;

use Illuminate\Http\Request;
use App\Http\Controllers\API\BaseController as BaseController;

class Instances extends BaseController {

	public function getInstance( Request $request ){
      try {
      	$data = [ "status" => true, "title" => "Ok", "msg" => "Send message has been successfully" ];
		$objData = new \stdClass();
        $objData->instanceId = \DB::table('company_info')->pluck('instance_id')[0] !== 0 ? \DB::table('company_info')->pluck('instance_id')[0] : intval(env('INSTANCE_ID'));
		$data['data'] = $objData;

         return response()->json($data);

        }catch(\Exception $e)
        {
            echo "Connection failed: " . $e->getMessage();
        }
    }

}
