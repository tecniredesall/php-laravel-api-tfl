<?php

namespace App;

class TransactionsOut extends Api
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
    protected $table = 'transactions_out';

    
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

    public function tanks(){
        //return $this->belongsTo( '\App\Tanks', 'tank' )->with( array( 'commodities', 'branchs' ) );
        return $this->belongsTo( '\App\Tanks', 'tank' );
    }
    public function buyers(){
        return $this->belongsTo( '\App\Buyers', 'buyer' )->orderBy( 'name', 'ASC' );
    }
    public function branchs(){
        return $this->belongsTo( '\App\Locations', 'branch_id' );
    }
    public function commodities(){
        //return $this->belongsTo( '\App\Commodities', 'commodity' )->with( 'branchs' );
        return $this->belongsTo( '\App\Commodities', 'commodity' )->orderBy( 'name', 'ASC' )->with( 'metas' );
    }
}
