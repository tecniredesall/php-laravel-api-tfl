<?php

namespace App\Http\Controllers\API\WEB;

use Illuminate\Http\Request;
use App\Http\Controllers\API\BaseController as BaseController;
use Illuminate\Support\Facades\Log;

class Buyers extends BaseController {

    private $permission = 6;

    /**
     * Enable this module.
     *
     * @return \Illuminate\Http\Response
     */
    public function __construct(){
        $this->middleware( 'candoit:' . $this->permission );
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index( Request $request ){
        try{
            return $this->sendSuccess( '', array( 'data' => \App\Buyers::mostrar( null, false, $request ) , 'pending' => \App\Cudrequest::cudrequest( ["Buyers"] ) ) );
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
        $validator = \Validator::make( $request->all(), [
            'name' => 'required'
        ]);

        if( $request->name !== null) {
            $lower = strtolower( trim( $request->name ) );
            $current = \App\Buyers::whereRaw("LOWER(name) = ". " '$lower' ")->where('status', '<>', 5)->first();
            if (!is_null($current)) {
                $validator->getMessageBag()->add('name', 'This name is already registered');
                return $this->sendError('Error', $validator->errors(), 409);
            }
        }

        if( isset($request->email) && $request->email !== null) {
            $lowerEmail = strtolower( trim( $request->email ) );
            $current = \App\Buyers::whereRaw("LOWER(email) = ". " '$lowerEmail' " )->where('status', '<>', 5)->first();
            if (!is_null($current)) {
                $validator->getMessageBag()->add('email', 'This email is already registered');
                return $this->sendError('Error', $validator->errors(), 409);
            }
        }

        try{
            if( $validator->fails() )
                return $this->sendError( 'Error', $validator->errors(), 400 );
            return $this->sendSuccess( 'Buyer has been created successfully', array( 'data' => \App\Buyers::guardar( $request, $id = null ) ) );
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
            return $this->sendSuccess( '', array( 'data' => \App\Buyers::mostrar( $id, false ) ) );
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
        ]);

        if( !(\App\Cudrequest::validUuid($id)) ){
            if( $request->name !== null) {
                $lower = strtolower( trim( $request->name ) );
                $current = \App\Buyers::whereRaw("LOWER(name) = ". " '$lower' ")->where('id', '<>', $id)->where('status', '<>', 5)->first();
                if (!is_null($current)) {
                    $validator->getMessageBag()->add('name', 'This name is already registered');
                    return $this->sendError('Error', $validator->errors(), 409);
                }
            }

            if( isset($request->email) && $request->email !== null) {
                $lowerEmail = strtolower( trim( $request->email ) );
                $current = \App\Buyers::whereRaw("LOWER(email) = ". " '$lowerEmail' " )->where('id', '<>', $id)->where('status', '<>', 5)->first();
                if (!is_null($current)) {
                    $validator->getMessageBag()->add('email', 'This email is already registered');
                    return $this->sendError('Error', $validator->errors(), 409);
                }
            }
        }

        try{
            if( $validator->fails() )
                return $this->sendError( 'Error', $validator->errors(), 400 );
            return $this->sendSuccess( 'Buyer has been updated successfully', array( 'data' => \App\Buyers::actualizar( $request, $id ) ) );
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
            return $this->sendSuccess( 'Buyer has been deleted successfully', array( 'data' => \App\Buyers::eliminar( $id ) ) );
        } catch( Exception $e ){
            Log::error($e->getMessage());
            return $this->sendError( 'Internal Server Error', array(), 500 );
        }
    }
}
