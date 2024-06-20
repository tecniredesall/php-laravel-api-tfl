<?php

namespace App;
use Illuminate\Support\Facades\DB;
class CommoditiesFeatures extends Api
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
    protected $table = 'commodities_features';


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
    public function branchs()
    {
        return $this->hasOne('\App\Locations', 'id', 'branch_id');
    }

    public function metas()
    {
        return $this->hasOne('\App\Metadata_commodities', 'commodity_id', 'id');
    }

    public function moistures()
    {
        return $this->hasMany('\App\Commodity_moistures', 'commodity_id', 'id');
    }

    public static function mostrar($id = null, $request = array())
    {
        $query = \App\CommoditiesFeatures::select('*')->get()->toArray();

        return response()->json($query);
    }

    private static function saveOrUpdate($request, $id)
    {
        \DB::beginTransaction();
        try {
            $obj = new self;
            $obj->commodities_features_id = $id;
            $obj->name = isset($request->name) ? $request->name : NULL;
            $obj->is_moisture = isset($request->is_moisture) ? $request->is_moisture : 0;
            $obj->can_delete_feature = isset($request->can_delete_feature) ? $request->can_delete_feature : 1;
            $obj->source_id = isset($request->source_id) ? $request->source_id : NULL;
            $obj->branch_id = isset($request->branch_id) ? $request->branch_id : 0;
            $obj->mstatus = isset($request->mstatus) ? $request->mstatus : 0;

            \App\Cudrequest::process('Commodities_Features', $obj, $id);

            \DB::commit();
            return self::mostrar(null, $request);
        } catch (Exception $e) {
            \DB::rollBack();
            return false;
        }
    }

    private static function del($id)
    {
        try {
            return \App\Cudrequest::delrequest($id, 'Commodities_Features');
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    protected static function guardar($request, $id)
    {
        return static::saveOrUpdate($request, $id);
    }

    protected static function actualizar($request, $id)
    {
        return static::saveOrUpdate($request, $id);
    }

    protected static function eliminar($id)
    {
        return static::del($id);
    }
}
