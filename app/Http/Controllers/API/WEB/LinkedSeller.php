<?php

namespace App\Http\Controllers\API\WEB;

use Illuminate\Http\Request;
use App\Sellers;
use App\Http\Controllers\API\BaseController as BaseController;
use Illuminate\Support\Facades\Log;

class LinkedSeller extends BaseController
{

    public function index( Request $request )
    {
        //
    }

    public function getSellers(Request $request){
        try{
            $inProcess = \App\Cudrequest::select('request')->where([['entity_name', 'LinkedSellers'], ['was_completed', 0] ])->get()->toArray();
            $sellers = \DB::table( 'sellers' )->select('id', 'name')->where([ ['id', '<>', $request->idSeller], ['status', '<>', 5], ['seller_id_parent', 0] ]);
            foreach( $inProcess as $k => $v) {
                $idinProcess = json_decode($inProcess[$k]['request'])->id;
                $sellers->where('id', '<>', $idinProcess);
            }

            $data = [  'sellers' => $sellers->get()->toArray() ];
            return response()->json( $data );
        } catch( Exception $e ){
            Log::error($e->getMessage());
            return $this->sendError( 'Internal Server Error', array(), 500 );
        }
    }

    public function linked(Request $request){
        try{
            if(\App\Cudrequest::validUuid($request["id"])){
                $id = $request["id"];
                \App\Cudrequest::delrequest($id, 'Sellers');
            }else {
                $id_seller = $request["id_seller"];
                $request = \App\Sellers::find(intval($request["linked_seller"]));
                $request = json_decode($request);
                $request->seller_id_parent = $id_seller !== null ? $id_seller : 0;
                \App\Cudrequest::process('LinkedSellers', $request, 2);
            }

            return response()->json( [ 'success' => true ] );
        } catch( Exception $e ){
            Log::error($e->getMessage());
            return $this->sendError( 'Internal Server Error', array(), 500 );
        }
    }

}
