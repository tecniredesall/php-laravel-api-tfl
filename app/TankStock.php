<?php

namespace App;

class TankStock extends Api
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
    protected $table = 'tankstock';

    
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

    protected static function mostrar( $id=null, $request=array() ){}

    private static function saveOrUpdate( $request, $id=null ){
        // \DB::beginTransaction();
        // try{
        //     if( !is_null( $id ) )
        //         $obj = self::find( $id );
        //     else
        //         $obj = new self;
        //     $obj->name = $request->name;
        //     $obj->capacity = $request->capacity;
        //     $obj->warn_min = $request->warn_min;
        //     $obj->warn_max = $request->warn_max;
        //     $obj->description = isset( $request->description ) ? $request->description : NULL;
        //     $obj->stock = isset( $request->stock ) ? $request->stock : 0;
        //     $obj->stock_lb = isset( $request->stock_lb ) ? $request->stock_lb : 0;
        //     $obj->stock_lbd = isset( $request->stock_lbd ) ? $request->stock_lbd : 0;
        //     $obj->status = isset( $request->status ) ? $request->status : 0;
        //     $obj->source_id = isset( $request->source_id ) ? $request->source_id : NULL;
        //     $obj->branch_id = isset( $request->branch_id ) ? $request->branch_id : NULL;
        //     if( isset( $request->id ) )
        //         $obj->push();
        //     else
        //         $obj->save();
        //     \DB::commit();
        //     return self::mostrar();
        // } catch( Exception $e ){
        //     \DB::rollBack();
        //     return false;
        // }
    }

    private static function del( $id ){
        // \DB::beginTransaction();
        // try{
        //     $obj = self::find( $id );
        //     $obj->status = 5;
        //     $obj->push();
        //     \DB::commit();
        //     return self::mostrar();
        // } catch( Exception $e ){
        //     \DB::rollBack();
        //     return false;
        // }
    }

    protected static function guardar( $request ){ return static::saveOrUpdate( $request ); }
    protected static function actualizar( $request, $id ){ return static::saveOrUpdate( $request, $id ); }
    protected static function eliminar( $request ){ return static::del( $request ); }
}
