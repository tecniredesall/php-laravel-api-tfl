<?php

namespace App;

class Buyers extends Api {

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    // public $timestamps = false;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'buyers';

    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];

    public static function mostrar( $id=null, $isMobile=false, $request=array() ){
        $string = isset( $request->q ) ? $request->q : '';
        $consulta = is_null( $id ) ? self::where( 'status', '<>', 5 )->whereRaw( "LOWER(name) LIKE ?", [ '%' . strtolower( $string ) . '%' ] )->orderBy('name', 'asc') : self::where( 'id', $id );
        $array = array();
        $cRows = $consulta->get()->count();
        $skip = isset( $request->page ) && $request->page != 1 ? ( $request->page * env( 'PER_PAGE' ) ) - env( 'PER_PAGE' ) : 0;
        $query = $consulta->skip( $skip )->take( env( 'PER_PAGE' ) )->get();

        foreach( $query as $key => $val ){
            $row = array();
            if( $isMobile ){
                $location = \DB::table( 'locations' )->where( 'id', $val->branch_id )->pluck( 'name' );
                $row[ 'id' ] = $val->id;
                $row[ 'name' ] = $val->name;
                $row[ 'contact' ] = is_null( $val->contact ) ? '': $val->contact;
                $row[ 'phone' ] = is_null( $val->phone ) ? '': $val->phone;
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
                $row = $val->toArray();   
            }
            if( !is_null( $id ) && $isMobile )
                $array = $row;
            else
                $array[] = $row;
        }
        if( $isMobile && !is_null( $id ) )
            return $array;
        else
            return parent::getPaginator( $array, $cRows, $request );
    }

    private static function saveOrUpdate( $request, $id ){
        \DB::beginTransaction();
        try{
            $obj = new self;
            $obj->id = $id;
            $obj->name = $request->name;
            $obj->email =  isset( $request->email ) ? $request->email : NULL;
            $obj->contact = isset( $request->contact ) ? $request->contact : NULL;
            $obj->phone = isset( $request->phone ) ? $request->phone : NULL;
            $obj->fax = isset( $request->fax ) ? $request->fax : NULL;
            $obj->mobile = isset( $request->mobile ) ? $request->mobile : NULL;
            $obj->address = isset( $request->address ) ? $request->address : NULL;
            $obj->group = isset( $request->group ) ? $request->group : 0;
            $obj->status = isset( $request->status ) ? $request->status : 0;
            $obj->source_id = isset( $request->source_id ) ? $request->source_id : NULL;
            $obj->branch_id = isset( $request->branch_id ) ? $request->branch_id : NULL;
            \App\Cudrequest::process( 'Buyers', $obj, $id );

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

            $del = \App\Cudrequest::process( 'Buyers', $request, $id, $action = 'delete'  );
            return $del;
            return self::mostrar( null, false, $request );
        } catch( Exception $e ){
            return false;
        }
    }

    protected static function guardar( $request ){ return static::saveOrUpdate( $request, $id = 0 ); }
    protected static function actualizar( $request, $id ){ return static::saveOrUpdate( $request, $id ); }
    protected static function eliminar( $id ){ return static::del( $id ); }
}
