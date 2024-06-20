<?php
namespace App\Http\Controllers\API\WEB;

use Illuminate\Http\Request;
use App\Http\Controllers\API\BaseController as BaseController;
use Illuminate\Support\Facades\Log;

class Locations extends BaseController
{
    private $permission = 27;

    /**
     * Enable this module.
     *
     * @return \Illuminate\Http\Response
     */
    public function __construct(){
        $this->middleware( 'candoit:' . $this->permission );
    }
    public function index( Request $request ){
        try{
            return $this->sendSuccess( '', array( 'data' => \App\Locations::mostrar( null, $request ) ) );
        } catch( \Exception $e ){
            Log::error($e->getMessage());
            return $this->sendError( 'Internal Server Error', array(), 500 );
        }
    }

    public function store( Request $request ){
        $rules = array(
            'name' => 'required',
            'pdfpath' => 'required',
            'latitude' => 'required',
            'longitude' => 'required'
        );
        $messages = array (
            'name.required' => 'Field required',
            'pdfpath.required' => 'Field required',
            'latitude.required' => 'Field required',
            'longitude.required' => 'Field required'
        );

        $validator = \Validator::make($request->all(), $rules, $messages);

        if( $request->name !== null) {
            $lower = strtolower(trim($request->name));
            $current = \App\Locations::whereRaw("LOWER(name) = " . " '$lower' ")->first();
            if (!is_null($current)) {
                $validator->getMessageBag()->add('name', 'This location name is already registered');
                return $this->sendError('Error', $validator->errors(), 409);
            }
        }

        try{
            if( $validator->fails() )
                return $this->sendError( 'Error', $validator->errors(), 400 );
            return $this->sendSuccess( 'Location has been created successfully', array( 'data' => \App\Locations::guardar( $request ) ) );
        } catch( \Exception $e ){
            Log::error($e->getMessage());
            return $this->sendError( 'Internal Server Error', array(), 500 );
        }
    }

    public function update( $id, Request $request )
    {
        $validator = \Validator::make($request->all(), [
            'name' => 'required'
        ]);

        if( $request->name !== null) {
            $lower = strtolower(trim($request->name));
            $current = \App\Locations::whereRaw("LOWER(name) = " . " '$lower' ")->where('id', '<>', $id)->where('status', '<>', 0)->first();
            if (!is_null($current)) {
                $validator->getMessageBag()->add('name', 'This location name is already registered');
                return $this->sendError('Error', $validator->errors(), 409);
            }
        }
        try{
            if( $validator->fails() )
                return $this->sendError( 'Error', $validator->errors(), 400 );
            return $this->sendSuccess( 'Location has been updated successfully', array( 'data' => \App\Locations::actualizar( $request, $id ) ) );
        } catch( \Exception $e ){
            Log::error($e->getMessage());
            return $this->sendError( 'Internal Server Error', array(), 500 );
        }
    }
    public function destroy( $id ){
        try{
            return $this->sendSuccess( 'Commodity has been deleted successfully', array( 'data' => \App\Locations::eliminar( $id ) ) );
        } catch( \Exception $e ){
            Log::error($e->getMessage());
            return $this->sendError( 'Internal Server Error', array(), 500 );
        }
    }
}