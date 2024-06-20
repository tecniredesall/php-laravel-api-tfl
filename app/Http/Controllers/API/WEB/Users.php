<?php

namespace App\Http\Controllers\API\WEB;

use Illuminate\Http\Request;
use App\Http\Controllers\API\BaseController as BaseController;
use Illuminate\Support\Facades\Log;

class Users extends BaseController {

    private $permission = 9;

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
            return $this->sendSuccess( '', array( 'data' => \App\Users::mostrar( null, $request ), 'pending' => \App\Cudrequest::cudrequest( ["Users", "Security_grant"] ) ) );
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
            'lastname' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:4'
        ];

        $messages = [
            'name.required' => 'Field required',
            'lastname.required' => 'Field required',
            'email.required' => 'Field required',
            'email.email' => 'Incorrect email',
            'password.required' => 'Field required'
        ];
        $validator = \Validator::make($request->all(), $rules, $messages);

        if( $request->email !== null) {
            $lowerEmail = strtolower( $request->email );
            $current = \App\Users::whereRaw("LOWER(email) = ". " '$lowerEmail' ")->where('status', '<>', 2)->first();
            if (!is_null($current)) {
                $validator->getMessageBag()->add('email', 'This email is already registered');
                return $this->sendError('Error', $validator->errors(), 409);
            }
        }

        if( $request->name !== null) {
            $lower = strtolower( trim( $request->name ) );
            $current = \App\Users::whereRaw("LOWER(name) = ". " '$lower' ")->where('status', '<>', 2)->first();
            if (!is_null($current)) {
                $validator->getMessageBag()->add('name', 'This name is already registered');
                return $this->sendError('Error', $validator->errors(), 409);
            }
        }

        try{
            if( $validator->fails() )
                return $this->sendError( 'Error', $validator->errors(), 400 );
            return $this->sendSuccess( 'User has been created successfully', array( 'data' => \App\Users::guardar( $request, $id = 0 ) ) );
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
            return $this->sendSuccess( '', array( 'data' => \App\Users::mostrar( $id, $request ) ) );
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
            'lastname' => 'required',
            'email' => 'required|email'
        ]);

        $UUIDv4 = '/^[0-9A-F]{8}-[0-9A-F]{4}-4[0-9A-F]{3}-[89AB][0-9A-F]{3}-[0-9A-F]{12}$/i';
        $cudreq =  preg_match($UUIDv4, $id);

        if( !$cudreq ){
            if( $request->email !== null) {
                $lowerEmail = strtolower( $request->email );
                $current = \App\Users::whereRaw("LOWER(email) = ". " '$lowerEmail' ")->where('id', '<>', $id)->where('status', '<>', 2)->first();
                if (!is_null($current)) {
                    $validator->getMessageBag()->add('email', 'This email is already registered');
                    return $this->sendError('Error', $validator->errors(), 409);
                }
            }

            if( $request->name !== null) {
                $lower = strtolower( trim( $request->name ) );
                $current = \App\Users::whereRaw("LOWER(name) = ". " '$lower' ")->where('id', '<>', $id)->where('status', '<>', 2)->first();
                if (!is_null($current)) {
                    $validator->getMessageBag()->add('name', 'This name is already registered');
                    return $this->sendError('Error', $validator->errors(), 409);
                }
            }
        }

        try{
            if( $validator->fails() )
                return $this->sendError( 'Error', $validator->errors(), 400 );
            return $this->sendSuccess( 'User has been updated successfully', array( 'data' => \App\Users::actualizar( $request, $id ) ) );
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
            if( \Auth::id() == $id )
                return $this->sendError( "It's not possible to erase yourself", array(), 406 );
            return $this->sendSuccess( 'User has been deleted successfully', array( 'data' => \App\Users::eliminar( $id ) ) );
        } catch( Exception $e ){
            Log::error($e->getMessage());
            return $this->sendError( 'Internal Server Error', array(), 500 );
        }
    }

    public function securityGrant(Request $request){
        $rules = [
            'user' => 'required',
            'process' => 'required',
            'action' => 'required'
        ];

        $messages = [
            'user.required' => 'Field required',
            'process.required' => 'Field required',
            'action.required' => 'Field required',
        ];
        $validator = \Validator::make($request->all(), $rules, $messages);
        try{
            if( $validator->fails() )
                return $this->sendError( 'Error', $validator->errors(), 400 );
            return $this->sendSuccess( 'User has been created successfully', array( 'data' => \App\Security_grant::guardar( $request ) ) );
        } catch( Exception $e ){
            Log::error($e->getMessage());
            return $this->sendError( 'Internal Server Error', array(), 500 );
        }
    }
}
