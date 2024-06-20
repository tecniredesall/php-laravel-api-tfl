<?php

namespace App\Http\Controllers\API\WEB;

use Illuminate\Http\Request;
use App\Http\Controllers\API\BaseController as BaseController;
use Illuminate\Support\Facades\Log;

class CommoditiesFeatures extends BaseController {

    public function index( Request $request ){
        try{
            return $this->sendSuccess( '', array( 'data' => \App\CommoditiesFeatures::mostrar( null, $request ), 'pending' => \App\Cudrequest::cudrequest( ["Commodities_Features"] ) ) );
        } catch( Exception $e ){
            Log::error($e->getMessage());
            return $this->sendError( 'Internal Server Error', array(), 500 );
        }
    }

    public function store( Request $request ){
        $rules = array(
            'name' => 'required',
        );
        $messages = array (
            'name.required' => 'Field required',
        );

        $validator = \Validator::make($request->all(), $rules, $messages);

        if( $request->name !== null) {
            $lower = strtolower( trim( $request->name ) );
            $current = \App\CommoditiesFeatures::whereRaw("LOWER(name) = ". " '$lower' ")->first();
            if (!is_null($current)) {
                $validator->getMessageBag()->add('name', 'This name is already registered');
                return $this->sendError('Error', $validator->errors(), 409);
            }
        }

        try{
            if( $validator->fails() )
                return $this->sendError( 'Error', $validator->errors(), 400 );
            return $this->sendSuccess( 'Commodity has been created successfully', array( 'data' => \App\CommoditiesFeatures::guardar( $request, $id = 0 ) ) );
        } catch( Exception $e ){
            Log::error($e->getMessage());
            return $this->sendError( 'Internal Server Error', array(), 500 );
        }
    }

    public function show( ){
        //
    }

    public function update( $id, Request $request ){
        $validator = \Validator::make( $request->all(), [
            'name' => 'required'
        ], ['name.required' => 'Field required']);

        if( !( \App\Cudrequest::validUuid($id) ) ){
            if( $request->name !== null) {
                $lower = strtolower( trim( $request->name ) );
                $current = \App\CommoditiesFeatures::whereRaw("LOWER(name) = ". " '$lower' ")->where('commodities_features_id', '<>', $id)->first();
                if (!is_null($current)) {
                    $validator->getMessageBag()->add('name', 'This name is already registered');
                    return $this->sendError('Error', $validator->errors(), 409);
                }
            }
        }

        try{
            if( $validator->fails() )
                return $this->sendError( 'Error', $validator->errors(), 400 );
            return $this->sendSuccess( 'Commodity has been updated successfully', array( 'data' => \App\CommoditiesFeatures::actualizar( $request, $id ) ) );
        } catch( Exception $e ){
            Log::error($e->getMessage());
            return $this->sendError( 'Internal Server Error', array(), 500 );
        }
    }


    public function destroy( $id )
    {
        $current = \App\Characteristics_cmodity_silosys::where('commodity_feature_id', $id)->first();
        if(!is_null($current)) {
            return $this->sendError('Error', 'This commodity feature exist in some contract', 406);
        }

        try{
            return $this->sendSuccess( 'Commodity has been deleted successfully', array( 'data' => \App\CommoditiesFeatures::eliminar( $id ) ) );
        } catch( Exception $e ){
            Log::error($e->getMessage());
            return $this->sendError( 'Internal Server Error', array(), 500 );
        }
    }
}
