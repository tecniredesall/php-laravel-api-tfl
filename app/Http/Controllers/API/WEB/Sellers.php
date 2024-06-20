<?php

namespace App\Http\Controllers\API\WEB;

use Illuminate\Http\Request;
use App\Http\Controllers\API\BaseController as BaseController;
use Illuminate\Support\Facades\Log;

class Sellers extends BaseController {
    
    private $permission = 7;

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
            return $this->sendSuccess( '', array( 'data' => \App\Sellers::mostrar( null, false, $request ), 'pending' => \App\Cudrequest::cudrequest( ["Sellers", "LinkedSellers", "Farms"] ) ) );
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
           // 'email' => 'unique:sellers|unique:users'
        ];
        $messages = [
            'name.required' => 'Field required',
            //'email.unique' => 'This email is already registered'
        ];
        $validator = \Validator::make( $request->all(), $rules, $messages );

        if( $request->email !== null) {
            $lowerEmail = strtolower( $request->email );
            $current = \App\Sellers::whereRaw("LOWER(email) = ". " '$lowerEmail' ")->where('status', '<>', 5)->first();
            if (!is_null($current)) {
                $validator->getMessageBag()->add('email', 'This email is already registered');
                return $this->sendError('Error', $validator->errors(), 409);
            }
        }

        if( $request->name !== null) {
            $lower = strtolower( trim( $request->name ) );
            $current = \App\Sellers::whereRaw("LOWER(name) = ". " '$lower' ")->where('status', '<>', 5)->first();
            if (!is_null($current)) {
                $validator->getMessageBag()->add('name', 'This name is already registered');
                return $this->sendError('Error', $validator->errors(), 409);
            }
        }

        try{
            if( $validator->fails() )
                return $this->sendError( 'Error', $validator->errors(), 400 );
            return $this->sendSuccess( 'Seller has been created successfully', array( 'data' => \App\Sellers::guardar( $request, $id = null ) ) );
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
    public function show( $id, Request $request ){
        try{
            return $this->sendSuccess( '', array( 'data' => \App\Sellers::mostrar( $id, false, $request ) ) );
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
            'name' => 'required',
        ], [
            'name.required' => 'Field required',
        ]);
//        $cudreq = strlen($id) > 10;
        $UUIDv4 = '/^[0-9A-F]{8}-[0-9A-F]{4}-4[0-9A-F]{3}-[89AB][0-9A-F]{3}-[0-9A-F]{12}$/i';
        $cudreq =  preg_match($UUIDv4, $id);

        if( !$cudreq ){
            if( $request->email !== null) {
                $lowerEmail = strtolower( $request->email );
                $current = \App\Sellers::whereRaw("LOWER(email) = ". " '$lowerEmail' ")->where('id', '<>', $id)->where('status', '<>', 5)->first();
                if (!is_null($current)) {
                    $validator->getMessageBag()->add('email', 'This email is already registered');
                    return $this->sendError('Error', $validator->errors(), 409);
                }
            }

            if( $request->name !== null) {
                $lower = strtolower( trim( $request->name ) );
                $current = \App\Sellers::whereRaw("LOWER(name) = ". " '$lower' ")->where('id', '<>', $id)->where('status', '<>', 5)->first();
                if (!is_null($current)) {
                    $validator->getMessageBag()->add('name', 'This name is already registered');
                    return $this->sendError('Error', $validator->errors(), 409);
                }
            }
        }

        try{
            if( $validator->fails() )
                return $this->sendError('Error', $validator->errors(), 400);
            return $this->sendSuccess( 'Seller has been updated successfully', array( 'data' => \App\Sellers::actualizar( $request, $id ) ) );
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
            return $this->sendSuccess( 'Seller has been deleted successfully', array( 'data' => \App\Sellers::eliminar( $id ) ) );
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
    public function reset( $id, $email, Request $request ){
        try{
            $obj = \App\Sellers::find( $id );
            \App\Api::sendResetPass( $obj->id, 'Sellers', 1, $email );
            return $this->sendSuccess( 'Email send successfully', array('reset'=> true) );
        } catch( \Exception $e ){
            return $this->sendError( 'Internal Server Error', array(), 500 );
        }
    }
}
