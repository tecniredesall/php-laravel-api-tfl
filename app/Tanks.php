<?php

namespace App;

class Tanks extends Api
{
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
    protected $table = 'tanks';

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
    public function commodities(){ return $this->belongsTo( '\App\Commodities', 'commodity' )->with('metas'); }
    public function location(){ return $this->belongsTo( '\App\Locations', 'branch_id' ); }
    public function stocks(){ return $this->hasOne( '\App\TankStock', 'tid', 'id' ); }

    protected static function mostrar( $id=null, $request=array() ){
        $query = is_null( $id ) ? self::with( array( 'commodities' ) )->where( 'status', '<>', 5 )->get() : self::with( array( 'commodities' ) )->where( 'id', $id )->get();
        return parent::getPaginator( $query->toArray(), 0, $request );
    }

    private static function saveOrUpdate( $request, $id ){
        \DB::beginTransaction();
        try{
            $obj = new self;
            $obj->id = isset( $request->source_id ) ? $request->source_id : 0;
            $obj->name = isset( $request->name) ? $request->name : NULL;
            $obj->capacity = isset( $request->capacity ) ? $request->capacity : 0;
            $obj->warn_min = isset( $request->warn_min ) ? $request->warn_min : 0;
            $obj->warn_max = isset( $request->warn_max ) ? $request->warn_max : 0;
            $obj->commodity = isset( $request->commodity ) ? $request->commodity : 0;
            $obj->description = isset( $request->description ) ? $request->description : NULL;
            $obj->stock = isset( $request->stock ) ? $request->stock : 0.000;
            $obj->stock_lb = isset( $request->stock_lb ) ? $request->stock_lb : 0.000;
            $obj->stock_lbd = isset( $request->stock_lbd ) ? $request->stock_lbd : 0.000;
            if($obj["id"] == 0)
            {
                $obj->status = 0;
            }
            else
            {
               $obj->status = isset( $request->status ) ? $request->status : 0;
            }
            $obj->source_id =  isset( $request->source_id ) ? $request->source_id : 0;
            $obj->branch_id = isset( $request->branch_id ) ? $request->branch_id : 0;
            \App\Cudrequest::process( 'Tanks', $obj, $id );

            \DB::commit();
            return self::mostrar( null, $request );
        } catch( Exception $e ){
            \DB::rollBack();
            return false;
        }
    }

    private static function del( $id, $branch ){
        try{
            return \App\Cudrequest::delrequest($id, 'Tanks', $branch);
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    protected static function guardar( $request, $id ){ return static::saveOrUpdate( $request, $id ); }
    protected static function actualizar( $request, $id ){ return static::saveOrUpdate( $request, $id ); }
    protected static function eliminar( $id, $branch ){ return static::del( $id, $branch ); }
}
