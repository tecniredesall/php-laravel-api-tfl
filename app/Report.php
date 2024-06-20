<?php
namespace App;

class Report extends Api
{
    protected $table = 'reports';
    protected $fillable = [
        'report_name', 'select_params', 'main_table', 'inner_params', 'where_params', 'input_params', 'order_params', 'group_params','created_at', 'updated_at'
    ];


}
