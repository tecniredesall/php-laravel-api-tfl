<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Metadata_commodities extends Model
{
    //
    public $timestamps = false;

    protected $fillable = [ 'icon_name', 'commodity_id'];
}
