<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Metadata_users extends Model
{
    //
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [ 'user_id', 'serial_number', 'target_arn' ];
}
