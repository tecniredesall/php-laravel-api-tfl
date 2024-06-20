<?php

namespace App;

class TransactionsIn extends Api
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
	protected $table = 'transactions_in';


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
	public function users(){
		return $this->belongsTo( '\App\Users', 'user' );
	}
	public function commodities(){
		//return $this->belongsTo( '\App\Commodities', 'commodity' )->with( 'branchs' );
		return $this->belongsTo( '\App\Commodities', 'commodity' )->orderBy( 'name', 'ASC' )->with( 'metas' );;
	}
	public function tanks(){
		//return $this->belongsTo( '\App\Tanks', 'tank' )->with( array( 'commodities', 'branchs' ) );
		return $this->belongsTo( '\App\Tanks', 'tank' );
	}
	public function sellers(){
		// return $this->belongsTo( '\App\Sellers', 'seller' )->with( 'farms' );
		return $this->belongsTo( '\App\Sellers', 'seller' )->orderBy( 'name', 'ASC' );
	}
	public function farms(){
		//return $this->belongsTo( '\App\Farms', 'farm' )->with('sellers');
		return $this->belongsTo( '\App\Farms', 'farm', 'id' );
	}
	public function branchs(){
		return $this->belongsTo( '\App\Locations', 'branch_id' );
	}
	// public function commoditiesOrder(){
	// 	return $this->belongsTo( '\App\Commodities', 'commodity' )->orderBy( 'name', 'ASC' )->with( 'metas' );
	// }
}
