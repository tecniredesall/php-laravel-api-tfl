<?php

namespace App;
use App\Cudrequest;
class Sellers extends Api {

    protected $table = 'sellers';

    protected $fillable = [];

    protected $hidden = [ 'password' ];

    public function farms(){
        $general = \App\Company_info::select('*')->pluck('decimals_in_general')[0];
        return $this->hasMany( '\App\Farms', 'seller' )->where( 'status', '<>', 5 )
            ->selectRaw('id, name, seller, address, status, FORMAT(farms.acres,' . $general . ') as acres');
    }

    public static function mostrar( $id=null, $isMobile=false, $request=array() ){
        $string = isset( $request->q ) ? $request->q : '';
        if( is_null( $id ) ){
            $consulta = self::with( array( 'farms' ) )->where( 'status', '<>', 5 )->whereRaw( "LOWER(name) LIKE ?", [ '%' . strtolower( $string ) . '%' ] )->orderBy('name', 'asc');
        } else{
            $consulta = self::with( array( 'farms' ) )->where( 'id', $id );
        }
        $cRows = $consulta->get()->count();
        $skip = isset( $request->page ) && $request->page != 1 ? ( $request->page * env( 'PER_PAGE' ) ) - env( 'PER_PAGE' ) : 0;
        $query = $consulta->skip( $skip )->take( env( 'PER_PAGE' ) )->get();
        $array = array();
        foreach( $query as $key => $val ){
            $row = array();
            if( $isMobile ){
                $location = \DB::table( 'locations' )->where( 'id', $val->branch_id )->pluck( 'name' );
                $row[ 'id' ] = $val->id;
                $row[ 'name' ] = $val->name;
                $row[ 'contact' ] = is_null( $val->contact ) ? '': $val->contact;
                $row[ 'phone' ] = is_null( $val->phone ) ? '': $val->phone;
                $row[ 'fax' ] = is_null( $val->fax ) ? '': $val->fax;
                $row[ 'fax' ] = is_null( $val->fax ) ? '': $val->fax;
                $row[ 'mobile' ] = is_null( $val->mobile ) ? '': $val->mobile;
                $row[ 'email'] = is_null( $val->email ) ? '': $val->email;
                $row[ 'address'] = is_null( $val->address ) ? '': $val->address;
                $row[ 'location'] = ( count( $location ) > 0 ) ? $location[ 0 ] : '';
                $row[ 'lat' ] = "20.7095083";
                $row[ 'lng' ] = "-103.4162558";
            } else {
                $val->lat = "20.7095083";
                $val->lng = "-103.4162558";

                $inProcess = \App\Cudrequest::select('request')->where([['entity_name', 'LinkedSellers'], ['was_completed', 0], ['error_code','<>', 0] ])->get()->toArray();
                $query = \DB::table( 'sellers' )->select('id', 'name')->where('status','<>',5)->where( 'seller_id_parent', '=', $val->id  );
                foreach( $inProcess as $k => $v) {
                    $idinProcess = json_decode($inProcess[$k]['request'])->id;
                    $query->where('id', '<>', $idinProcess);
                }

                $val->linked = $query->get();
                $row = $val;
            }
            if( !is_null( $id ) && $isMobile )
                $array = $row;
            else
                $array[] = $row;
        }

        if( $isMobile && !is_null( $id ) )
            return $array;
        else
            return \App\Api::getPaginator( $array, $cRows, $request );
    }

    private static function saveOrUpdate( $request, $id ){
        \DB::beginTransaction();
        try{
            $obj = new self;
            $obj->id = $id;
            $obj->name = isset($request->name ) ? $request->name : NULL;
            if( isset( $request->password ) )
                $obj->password = bcrypt( $request->password );
            $obj->email = isset( $request->email ) ? $request->email : NULL;
            $obj->contact = isset( $request->contact ) ? $request->contact : NULL;
            $obj->phone = isset( $request->phone ) ? $request->phone : NULL;
            $obj->fax = isset( $request->fax ) ? $request->fax : NULL;
            $obj->mobile = isset( $request->mobile ) ? $request->mobile : NULL;
            $obj->address = isset( $request->address ) ? $request->address : NULL;
            $obj->seller_id_parent = isset( $request->seller_id_parent ) ? $request->seller_id_parent : 0;
            $obj->status = isset( $request->status ) ? $request->status : 1;
            $obj->source_id = isset( $request->source_id ) ? $request->source_id : NULL;
            $obj->branch_id = isset( $request->branch_id ) ? $request->branch_id : NULL;

            \App\Cudrequest::process( 'Sellers', $obj, $id );

            \DB::commit();
            return self::mostrar( null, false, $request );
        } catch( Exception $e ){
            \DB::rollBack();
            return false;
        }
    }


    private static function del( $id ){
        try{
            $currentObject = \App\Cudrequest::where([['cudrequest_id', $id], ['was_completed', 0]])->first();
            $cudreq = !empty($currentObject);

            if( $cudreq ) {
                $request = \App\Cudrequest::select('request')->where('cudrequest_id',$id)->first();
                $request = json_decode($request["request"]);
                $request->id = 0;
                $request->password = '';
            }else {
                $request = self::find($id);
                if($request !== null){
                    $request = $request;
                }else{
                    return 'ID does not exist';
                }
            }
            $del = \App\Cudrequest::process( 'Sellers', $request, $id, $action = 'delete'  );
            return $del;
        } catch( Exception $e ){
            return false;
        }
    }

    protected static function guardar( $request ){ return static::saveOrUpdate( $request, $id = 0 ); }
    protected static function actualizar( $request, $id ){ return static::saveOrUpdate( $request, $id ); }
    protected static function eliminar( $id ){ return static::del( $id ); }
}
