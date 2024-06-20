<?php

namespace App;

class Security_grant extends Api
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
    protected $table = 'security_grant';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['user', 'process'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];

    /**
     * Get the phone record associated with the user.
     */


    private static function saveOrDelete($request)
    {
        try {
            $obj = new self;
            $obj->user = isset($request->user) ? $request->user : NULL;
            $obj->process = isset($request->process) ? $request->process : NULL;

            if( $request->action == 1) {
                \App\Cudrequest::process( 'Security_grant', $obj, $request->id, $action = 'process');
            }else{
                if(isset($request->id))
                    $request->id;

                $del = \App\Cudrequest::process( 'Security_grant', $obj, $request->id, $action = 'delete'  );
                return $del;
            }
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }



    protected static function guardar($request){ return static::saveOrDelete($request); }

}
