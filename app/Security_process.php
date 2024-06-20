<?php

namespace App;

class Security_process extends Api
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
    protected $table = 'security_process';

    
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

    protected static function index( $lang ){
        $l = '';
        if( $lang == '' )
            $l = 'en';
        elseif( $lang == 'en' )
            $l = 'en';
        else
            $l = 'es';

        if( $l == 'en' )
            return self::get( array( 'id', 'description', 'status' ) );
        else
            return self::get( array( 'id', 'description_spa As description', 'status' ) );
    }
}
