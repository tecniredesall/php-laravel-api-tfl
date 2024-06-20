<?php
namespace App;

class Farms extends Api {
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
    protected $table = 'farms';

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

    /**
     * Get the phone record associated with the user.
     */

    public function sellers(){
    	return $this->belongsTo( '\App\Sellers', 'seller', 'id' );
    }

    public static function mostrar( $id=null, $isMobile=false, $request=array() ){
        return is_null( $id ) ? parent::getPaginator( self::where( 'status', '<>', 5 )->get()->toArray(), 0, $request ) : self::where( 'id', $id )->get()->toArray();
    }

    private static function saveOrUpdate( $request, $id ){
        \DB::beginTransaction();
        try{
            $obj = new self;
            $obj->id = $id;
            $obj->name = $request->name;
            $obj->seller = $request->seller;
            $obj->status = isset( $request->status ) ? $request->status : 0;
            $obj->acres =  isset( $request->acres ) ? str_replace(',', '', $request->acres)  : 0;
            $obj->address = isset( $request->address ) ? $request->address : NULL;
            $obj->source_id = isset( $request->source_id ) ? $request->source_id : NULL;
            $obj->branch_id = isset( $request->branch_id ) ? $request->branch_id : NULL;
            $obj->mstatus = 2;

            \App\Cudrequest::process( 'Farms', $obj, $id );

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
                $request = \App\Cudrequest::select('request')->where('cudrequest_id', $id)->first();
                $request = json_decode($request["request"]);
                $request->id = 0;
            }else {
                $request = self::find($id);
                if ($request !== null) {
                    $request = $request;
                } else {
                    return 'ID does not exist';
                }
            }
            $del = \App\Cudrequest::process( 'Farms', $request, $id, $action = 'delete'  );

            return $del;
        } catch( Exception $e ){

            return false;
        }
    }

    protected static function guardar( $request ){ return static::saveOrUpdate( $request, $id = 0 ); }
    protected static function actualizar( $request, $id ){ return static::saveOrUpdate( $request, $id ); }
    protected static function eliminar( $id ){ return static::del( $id ); }
}
