<?php

namespace App\Models;

use App\Filter\Filterable;
use Illuminate\Database\Eloquent\Model;

class CmodityLoads extends Model
{
    use Filterable;
    protected $primaryKey = 'cmodity_load_id';
    public $incrementing = false;
    public $timestamps=false;

    public function getResponseAttribute()
    {
        $value= $this->attributes['response'];
        if(!empty($value)){
                return json_decode($value);
        }

    }

}
