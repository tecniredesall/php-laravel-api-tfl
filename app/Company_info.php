<?php

namespace App;

class Company_info extends Api
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
    protected $table = 'company_info';

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

    public static function guardar($data)
    {
        \DB::beginTransaction();
        try {
            $obj = new self;
            $id = isset($data->id) ? $data->id : 1;
            $obj->name = isset($data->name) ? $data->name : NULL;
            $obj->phone = isset($data->phone) ? $data->phone : NULL;
            $obj->email = isset($data->email) ? $data->email : NULL;
            $obj->address = isset($data->address) ? $data->address : NULL;
            $obj->decimals_for_money = isset($data->decimals_for_money) ? $data->decimals_for_money : NULL;
            $obj->decimals_in_tickets = isset($data->decimals_in_tickets) ? $data->decimals_in_tickets : NULL;
            $obj->decimals_in_general = isset($data->decimals_in_general) ? $data->decimals_in_general : NULL;
            $obj->metric_system_id = isset($data->metric_system_id) ? $data->metric_system_id : 0;
            $obj->default_location = isset($data->default_location) ? $data->default_location : 1;
            $obj->url = isset(self::pluck('url')[0]) ? self::pluck('url')[0] : NULL;
            $obj->api_url = isset(self::pluck('api_url')[0]) ? self::pluck('api_url')[0] : NULL;
            $obj->sqs_key = isset($data->sqs_key) ? $data->sqs_key : NULL;
            $obj->sqs_secret = isset($data->sqs_secret) ? $data->sqs_secret : NULL;
            $obj->sqs_name = isset($data->sqs_name) ? $data->sqs_name : NULL;
            $obj->sqs_url = isset($data->sqs_url) ? $data->sqs_url : NULL;
            $obj->sqs_arn = isset($data->sqs_arn) ? $data->sqs_arn : NULL;
            $obj->display_show_id = isset($data->display_show_id) ? $data->display_show_id : 0;
            $obj->display_show_id_in_print = isset($data->display_show_id_in_print) ? $data->display_show_id_in_print : 0;
            \DB::commit();

            \App\Cudrequest::process('Company_info', $obj, $id);

        } catch (\Exception $e) {
            \DB::rollBack();
            return false;
        }

    }

}
