<?php

namespace App;
use Illuminate\Support\Facades\DB;
class Commodities extends Api
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
    protected $table = 'commodities';


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
        $moistures = [
            'lmoist' => [0.0, 15.1, 16.1, 17.1, 18.1, 19.1, 20.1, 21.1, 22.1, 23.1, 24.1, 25.1, 26.1, 27.1],
            'hmoist' => [15.0, 16.0, 17.0, 18.0, 19.0, 20.0, 21.0, 22.0, 23.0, 24.0, 25.0, 26.0, 27.0, 100.0],
            'discount' => [0.00, 0.05, 0.10, 0.15, 0.20, 0.25, 0.30, 0.35, 0.40, 0.45, 0.50, 0.55, 0.60, 0.65],
            'dryshper' => [100.00, 98.50, 97.00, 95.50, 94.00, 92.50, 91.00, 89.50, 88.00, 86.50, 85.00, 83.50, 82.00, 80.50]
        ];
        $rro = array();
        for ($x = 0; $x <= 13; $x++)
            $rro[] = array(
                'lmoist' => $moistures['lmoist'][$x],
                'hmoist' => $moistures['hmoist'][$x],
                'discount' => $moistures['discount'][$x],
                'dryshper' => $moistures['dryshper'][$x]
            );
        $general = \App\Company_info::selectRaw('decimals_in_general')->pluck('decimals_in_general')[0];
        $consulta=self::with('metas', 'moistures')
            ->selectRaw("commodities.*, commodities.id as id_commod, FORMAT(commodities.stock," . $general .") as stock, FORMAT(commodities.avalue," . $general .") as avalue, FORMAT(commodities.warning_level," . $general .") as warning_level, FORMAT(commodities.price," . $general .") as price");

        if (is_null($id)) {
            $consulta ->where('status', '<>', 5)->orderBy('name', 'asc');
        } else {
            $consulta->where('id', $id);
        }
        $query = ($consulta->paginate($request->input('limit', env('PER_PAGE'))))->toArray();

        $items =&$query['data'];
        foreach ($items as $key => &$val) {
            $stock = DB::select('SELECT FORMAT(commostock.stock," . $general .") as stock FROM commostock WHERE commodity = '. $val['id_commod']);
            $val['stock'] = !empty($stock) ? $stock[0]->stock : 0;
            $val['moistures'] = (count($val['moistures']) > 0) ? $val['moistures'] : $rro;
        }
        return $query;
    }

    private static function saveOrUpdate($request, $id)
    {
        \DB::beginTransaction();
        try {
                $obj = new self;
                $obj->id = $id;
                $obj->name = isset($request->name) ? $request->name : NULL;
                $obj->status = isset($request->status) ? $request->status : NULL;
                $obj->warning_level = isset($request->warning_level) ? $request->warning_level : 0;
                $obj->price = isset($request->price) ? $request->price : 0;
                $obj->shrinkable = isset($request->shrinkable) ? $request->shrinkable : 0;
                $obj->stock = isset($request->stock) ? $request->stock : 0;
                $obj->stockd = isset($request->stockd) ? $request->stockd : 0;
                $obj->buyer = isset($request->buyer) ? $request->buyer : 0;
                $obj->label = isset($request->label) ? $request->label : 0;
                $obj->atype = isset($request->atype) ? $request->atype : 0;
                $obj->avalue = isset($request->avalue) ? $request->avalue : 0.0000;
                $obj->unitonticket = isset($request->unitonticket) ? $request->unitonticket : 0;
                $obj->selling_price = isset($request->selling_price) ? $request->selling_price : 0;
                $obj->qbid = isset($request->qbid) ? $request->qbid : NULL;
                $obj->tonstypetoprint = isset($request->tonstypetoprint) ? $request->tonstypetoprint : 0;
                $obj->source_id = isset($request->source_id) ? $request->source_id : NULL;
                $obj->commodity_general_id = isset($request->commodity_general_id) ? $request->commodity_general_id : NULL;
                $obj->branch_id = isset($request->branch_id) ? $request->branch_id : 0;
                $meta_comm = \App\Metadata_commodities::where('commodity_id', $id)->first();

                if ( $request->icon && !(\App\Cudrequest::validUuid($id)) ) {

                    if ( $meta_comm )
                    {
                        $icon_name = $request->metas['icon_name'] !== null ? $request->metas['icon_name'] : 'others';
                        \App\Metadata_commodities::where('id', $meta_comm->id)->update(['icon_name' => $icon_name]);
                    }
                    else if ( \App\Commodities::where('id', $id)->first() && is_null($meta_comm) )
                    {
                        $icon_name = $request->metas['icon_name'] ? $request->metas['icon_name'] : 'others';
                        \App\Metadata_commodities::insert(
                            ['icon_name' => $icon_name, 'commodity_id' => $id]
                        );
                    }
                } else {
                    if ( $meta_comm )
                    {
                        $icon_name = $request->metas['icon_name'] !== null ? $request->metas['icon_name'] : 'others';
                        \App\Metadata_commodities::where('id', $meta_comm->id)->update(['icon_name' => $icon_name]);
                    }
                    else if ( \App\Commodities::where('id', $id)->first() && is_null($meta_comm) )
                    {
                        $icon_name = $request->metas['icon_name'] ? $request->metas['icon_name'] : 'others';
                        \App\Metadata_commodities::insert(
                            ['icon_name' => $icon_name, 'commodity_id' => $id]
                        );
                    }
                    \App\Cudrequest::process('Commodities', $obj, $id);
                }

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
            return \App\Cudrequest::delrequest($id, 'Commodities');
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
