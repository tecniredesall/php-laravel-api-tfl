<?php

namespace App\Http\Controllers\API\WEB;

use Illuminate\Http\Request;
use App\Http\Controllers\API\BaseController as BaseController;
use Illuminate\Support\Facades\Log;

class Tanks extends BaseController {

    private $permission = 5;

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
            return $this->sendSuccess( '', array( 'data' => \App\Tanks::mostrar() ) );
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
            'name' => 'required',
            'description' => 'required',
            'capacity' => 'required|numeric',
            'warn_min' => 'required|numeric',
            'warn_max' => 'required|numeric',
        ],[
            'name.required' => 'Field required'
        ]);

        if( $request->stock > 0 ) {
            if ($request->capacity < $request->stock) {
                $validator->getMessageBag()->add('capacity', 'There is not enough capacity in the tank.');
                return $this->sendError('Error', $validator->errors(), 406);
            }
        }
        if( $request->name !== null) {
            $lower = strtolower( trim( $request->name ) );
            $current = \App\Tanks::whereRaw("LOWER(name) = ". " '$lower' ")->where('branch_id', $request->branch_id)->where('status', '<>', 5)->first();
            if (!is_null($current)) {
                $validator->getMessageBag()->add('name', 'This name is already registered');
                return $this->sendError('Error', $validator->errors(), 409);
            }
        }

        try{
            if( $validator->fails() )
                return $this->sendError( 'Error', $validator->errors(), 400 );
            return $this->sendSuccess( 'Buyer has been created successfully', array( 'data' => \App\Tanks::guardar( $request, $id = 0 ) ) );
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
            return $this->sendSuccess( '', array( 'data' => \App\Tanks::mostrar( $id, false ) ) );
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
            'description' => 'required',
            'capacity' => 'required|numeric',
            'warn_min' => 'required|numeric',
            'warn_max' => 'required|numeric',
        ],[
            'name.required' => 'Field required'
        ]);

        if( $request->stock > 0 ){
            if( $request->capacity < $request->stock) {
                $validator->getMessageBag()->add('capacity', 'There is not enough capacity in the tank.');
                return $this->sendError('Error', $validator->errors(), 406);
            }
        }

        if( !( \App\Cudrequest::validUuid($id) ) ){
            if( $request->name !== null) {
                $lower = strtolower( trim( $request->name ) );
                $current = \App\Tanks::whereRaw("LOWER(name) = ". " '$lower' ")->where('source_id', '<>', $id)->where('branch_id', $request->branch_id)->where('status', '<>', 5)->first();
                if (!is_null($current)) {
                    $validator->getMessageBag()->add('name', 'This name is already registered');
                    return $this->sendError('Error', $validator->errors(), 409);
                }
            }
        }

        try{
            if( $validator->fails() )
                return $this->sendError( 'Error', $validator->errors(), 400 );
            return $this->sendSuccess( 'Buyer has been updated successfully', array( 'data' => \App\Tanks::actualizar( $request, $id ) ) );
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
    public function delete_tanks( Request $request ){
        try{
            $validator = \Validator::make([], []);

            $tank = \App\Tanks::where('source_id', $request->id)->where('branch_id', $request->branch_id)->select('stock_lb')->first();
            if(!empty($tank)){
                if(isset($tank['stock_lb']) && ( $tank['stock_lb'] > 0 ) ) {
                    $validator->getMessageBag()->add('delete', 'Error while trying to delete the tank');
                    return $this->sendError('Error', $validator->errors(), 409);
                }
            }

            return $this->sendSuccess( 'Tank has been deleted successfully', array( 'data' => \App\Tanks::eliminar( $request->id, $request->branch_id ) ) );
        } catch( Exception $e ){
            Log::error($e->getMessage());
            return $this->sendError( 'Internal Server Error', array(), 500 );
        }
    }

    /**
     * Reset the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function reset( $id, Request $request ){
        try{
            $default_location  = $request->branch_id !== null  ? $request->branch_id : \App\Company_info::pluck('default_location')[0];
            \App\Tanks::where('id', $id)->update(['in_revert_process' => 1]);
            \App\SQS::send([
                'destination' => $default_location,
                'action' => 'resetTank',
                'type' => 'REQUEST',
                'group' => 'tanks',
                'message' => json_encode(['tankID' => isset($request->source_id) ? $request->source_id : 0, 'userID' => isset($request->user_id) ? $request->user_id : 0])
            ], 'local', $default_location, null);
            return $this->sendSuccess( 'Tank has been reseted successfully', [] );
        } catch( \Exception $e ){
            Log::error($e->getMessage());
            return $this->sendError( 'Internal Server Error', array(), 500 );
        }
    }



}
