<?php

namespace App\Http\Controllers\API\WEB;

use Illuminate\Http\Request;
use App\Http\Controllers\API\BaseController as BaseController;
use Illuminate\Support\Facades\Log;

class Farms extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index( Request $request ){
        try{
            return $this->sendSuccess( '', array( 'data' => \App\Farms::mostrar( null, false, $request ), 'pending' => \App\Cudrequest::cudrequest( ["Farms"] ) ) );
        } catch( Exception $e ){
            Log::error($e->getMessage());
            return $this->sendError( 'Internal Server Error', array(), 500 );
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store( Request $request ){
        $rules = [
            'name' => 'required',
        ];
        $messages = [
            'name.required' => 'Field required',
        ];
        $validator = \Validator::make( $request->all(), $rules, $messages );

        if( $request->name !== null) {
            $lower = strtolower( trim( $request->name ) );
            $current = \App\Farms::whereRaw("LOWER(name) = ". " '$lower' ")->where('status', '<>', 5)->first();
            if (!is_null($current)) {
                $validator->getMessageBag()->add('name', 'This name is already registered');
                return $this->sendError('Error', $validator->errors(), 409);
            }
        }

        try{
            if( $validator->fails() )
                return $this->sendError( 'Error', $validator->errors(), 400 );
            return $this->sendSuccess( 'Farm has been created successfully', array( 'data' => \App\Farms::guardar( $request, $id = null ) ) );
        } catch( Exception $e ){
            Log::error($e->getMessage());
            return $this->sendError( 'Internal Server Error', array(), 500 );
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show( $id ){
        try{
            return $this->sendSuccess( '', array( 'data' => \App\Farms::mostrar( $id, false ) ) );
        } catch( Exception $e ){
            Log::error($e->getMessage());
            return $this->sendError( 'Internal Server Error', array(), 500 );
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update( $id, Request $request ){
        $validator = \Validator::make( $request->all(), [
            'name' => 'required'
        ], [
            'name.required' => 'Field required',
        ]);

        if( \App\Cudrequest::validUuid($id) ){
            if( $request->name !== null) {
                $lower = strtolower( trim( $request->name ) );
                $current = \App\Farms::whereRaw("LOWER(name) = ". " '$lower' ")->where('id', '<>', $id)->where('status', '<>', 5)->first();
                if (!is_null($current)) {
                    $validator->getMessageBag()->add('name', 'This name is already registered');
                    return $this->sendError('Error', $validator->errors(), 409);
                }
            }
        }

        try{
            if( $validator->fails() )
                return $this->sendError( 'Error', $validator->errors(), 400 );
            return $this->sendSuccess( 'Farm has been updated successfully', array( 'data' => \App\Farms::actualizar( $request, $id ) ) );
        } catch( Exception $e ){
            Log::error($e->getMessage());
            return $this->sendError( 'Internal Server Error', array(), 500 );
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy( $id ){
        try{
            return $this->sendSuccess( 'Farm has been deleted successfully', array( 'data' => \App\Farms::eliminar( $id ) ) );
        } catch( Exception $e ){
            Log::error($e->getMessage());
            return $this->sendError( 'Internal Server Error', array(), 500 );
        }
    }
}
