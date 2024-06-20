<?php

use Illuminate\Database\Seeder;

class ReportsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table("reports")->insert([
            [
                "report_name"	=>	'REC_TICKET_REPORT',
                "field_report"	=>	'["DATE", "TICKET#", "FIELD TICKET", "LIC PLATE", "FIELD NAME", "LOCATION", "MOISTURE", "NET WT", "DRY WT"]',
                "select_params"	=>	'transactions_in.id, transactions_in.date_end, transactions_in.moisture, transactions_in.netdrywt, commodities.id as commoditie_id, commodities.name as commoditie_name, sellers.id as seller_id, sellers.name as seller_name, farms.id as farm_id, farms.name as farm_name,transactions_in.status, transactions_in.source_id, locations.id as location_id,locations.name as location_name, transactions_in.net, transactions_in.orgticket, transactions_in.trailerlicense',
                "main_table"	=>	'transactions_in',
                "inner_params"	=>	'{"commodities": ["join", "transactions_in.commodity", "commodities.id"],"sellers": ["join", "transactions_in.seller", "sellers.id"],"farms": ["leftjoin", "transactions_in.farm", "farms.id"],"locations": ["join", "transactions_in.branch_id", "locations.id"]}',
                "where_params"	=>	'{"transactions_in.status" : ["=" ,"2"]}',
                "input_params"	=>	'start_date, end_date',
                "order_params"	=>	'commodities.name, sellers.name, farms.name, transactions_in.id',
                "group_params"	=>	'["commodities.id", "sellers.id", "farms.id"]',
                "group_by"	=>	'commodities.id, sellers.id, farms.id',
                "group_by_select"	=>	'commodities.name as commoditie_name, commodities.id as commoditie_id, sellers.name as seller_name, sellers.id as seller_id, farms.name as farm_name, farms.id as farm_id, sum(transactions_in.net) as totalnet,sum(transactions_in.netdrywt) as totalnetdrywt'
            ],
            [
                "report_name" => "FSA_REC_TKT_REPORT",
                "field_report"	=>  '["DATE", "TICKET#", "FIELD TICKET", "FARM NAME", "MOISTURE", "TEST WT", "LBS NET WT",   "LBS DRY WT", "BU NET WT",  "BU DRY WT"]',
                "select_params" => 'transactions_in.id, transactions_in.date_end, transactions_in.moisture, transactions_in.netdrywt, commodities.id as commoditie_id, commodities.name as commoditie_name, sellers.id as seller_id, sellers.name as seller_name, farms.id as farm_id, farms.name as farm_name, transactions_in.status, transactions_in.net, transactions_in.testwt, transactions_in.orgticket',
                "main_table" => "transactions_in",
                "inner_params" => '{"commodities": ["join", "transactions_in.commodity", "commodities.id"],"sellers": ["join", "transactions_in.seller", "sellers.id"],"farms": ["leftjoin", "transactions_in.farm", "farms.id"]}',
                "where_params" => '{"transactions_in.status" : ["=" ,"2"]}',
                "input_params" => 'start_date, end_date',
                "order_params" => 'commodities.name, sellers.name, farms.name, transactions_in.id','["commodities.id", "sellers.id", "farms.id"]',
                "group_params" => '["commodities.id", "sellers.id", "farms.id"]',
                "group_by"	=>  'commodities.id, sellers.id, farms.id',
                "group_by_select"	=>  'commodities.name as commoditie_name, commodities.id as commoditie_id, sellers.name as seller_name, sellers.id as seller_id, farms.name as farm_name, farms.id as farm_id, sum(transactions_in.net) as totalnet,sum(transactions_in.netdrywt) as totalnetdrywt'
            ],
            [
                "report_name" => 'REC_TICKET_BYDRIVER',
                "field_report"	=> '["DATE", "TICKET#", "COMMODITY" , "LOCATION NAME", "FIELD NAME",  "MOISTURE", "NET WT", "DRY WT"]',
                "select_params" => 'transactions_in.id, transactions_in.date_end, transactions_in.moisture, transactions_in.netdrywt, commodities.id as commoditie_id, commodities.name as commoditie_name, sellers.id as seller_id, sellers.name as seller_name, farms.id as farm_id, farms.name as farm_name, transactions_in.source_id, locations.name as location_name, transactions_in.net',
                "main_table" => "transactions_in",
                "inner_params" => '{"commodities": ["join", "transactions_in.commodity", "commodities.id"], "sellers": ["join", "transactions_in.seller", "sellers.id"], "farms": ["leftjoin", "transactions_in.farm", "farms.id"], "locations": ["join", "transactions_in.branch_id", "locations.id"]}',
                "where_params" => '',
                "input_params" => 'start_date, end_date, commodities, sellers, farms',
                "order_params" => 'commodities.name, sellers.name, farms.name, transactions_in.id',
                "group_params" => '["commodities.id", "sellers.id", "farms.id"]',
                "group_by"	=>  'commodities.id, sellers.id, farms.id',
                "group_by_select"	=> 'commodities.name as commoditie_name, commodities.id as commoditie_id, sellers.name as seller_name, sellers.id as seller_id, farms.name as farm_name, farms.id as farm_id, sum(transactions_in.net) as totalnet,sum(transactions_in.netdrywt) as totalnetdrywt'
        ],
            [
                "report_name" => 'REC_TICKET_COMSUM',
                "field_report"	=> '["LOCATION NAME", "COMMODITY", "SUM(NET)", "SUM(NET DRY WT)"]',
                "select_params" => 'commodities.name as commoditie_name, locations.name as location_name, sum(transactions_in.net) as totalnet,sum(transactions_in.netdrywt) as totalnetdrywt',
                "main_table" => "transactions_in",
                "inner_params" => '{"commodities": ["join", "transactions_in.commodity", "commodities.id"],"sellers": ["join", "transactions_in.seller", "sellers.id"],"farms": ["leftjoin", "transactions_in.farm", "farms.id"],"locations": ["join", "transactions_in.branch_id", "locations.id"]}',
                "where_params" => '{"transactions_in.status" : ["=" ,"2"]}',
                "input_params" => 'start_date, end_date',
                "order_params" => '',
                "group_params" => '',
                "group_by"	=>  'locations.id, commodities.id',
                "group_by_select"	=> 'commodities.name as commoditie_name, locations.name as location_name, sum(transactions_in.net) as totalnet,sum(transactions_in.netdrywt) as totalnetdrywt'
        ],
            [
                "report_name" => '',
                "field_report"	=> '',
                "select_params" => '',
                "main_table" => '',
                "inner_params" => '',
                "where_params" => '',
                "input_params" => '',
                "order_params" => '',
                "group_params" => '',
                "group_by"	=>  '',
                "group_by_select"	=> ''
        ],
            [
                "report_name" => 'REC_TICKET_REPORTBYSILO',
                "field_report"	=> '["DATE", "TICKET#", "FIELD TICKET", "LIC PLATE", "FIELD NAME", "LOCATION", "MOISTURE", "NET WT", "DRY WT"]',
                "select_params" => 'transactions_in.id,  DATE(transactions_in.date_end) as date_end, transactions_in.moisture, transactions_in.netdrywt, commodities.id as commoditie_id, commodities.name as commoditie_name, farms.id as farm_id, farms.name as farm_name, transactions_in.status, transactions_in.source_id, locations.id as location_id,locations.name as location_name, transactions_in.net, transactions_in.orgticket, transactions_in.trailerlicense',
                "main_table" => "transactions_in",
                "inner_params" => '{"commodities": ["join", "transactions_in.commodity", "commodities.id"], "farms": ["leftjoin", "transactions_in.farm", "farms.id"],"locations": ["join", "transactions_in.branch_id", "locations.id"]}',
                "where_params" => '{"transactions_in.status" : ["=" ,"2"]}',
                "input_params" => 'start_date, end_date, locations',
                "order_params" => 'locations.name, commodities.name',
                "group_params" => '["locations.id", "commodities.id", "transactions_in.date_end"]',
                "group_by"	=>  'locations.id, commodities.id, DATE(transactions_in.date_end)',
                "group_by_select"	=> 'DATE(transactions_in.date_end) as date_end, commodities.id as commoditie_id, commodities.name as commoditie_name,locations.id as location_id,locations.name as location_name, sum(transactions_in.net) as totalnet,sum(transactions_in.netdrywt) as totalnetdrywt'
        ],
            [
                "report_name" => 'REC_TICKET_SUMMARY',
                "field_report"	=> '["LOT", "DRY WEIGHT", "NET"]',
                "select_params" => 'commodities.name as commoditie_name, commodities.id as commoditie_id, sellers.name as seller_name,sellers.id as seller_id, farms.name as farm_name, farms.id as farm_id,sum(transactions_in.net) as totalnet,sum(transactions_in.netdrywt) as totalnetdrywt',
                "main_table" => "transactions_in",
                "inner_params" => '{"tanks": ["join", "transactions_in.tank", "tanks.id"], "commodities": ["join", "transactions_in.commodity", "commodities.id"],"sellers": ["join", "transactions_in.seller", "sellers.id"],"farms": ["join", "transactions_in.farm", "farms.id"]}',
                "where_params" => '{"transactions_in.status" : ["=" ,"2"]}',
                "input_params" => 'start_date, end_date',
                "order_params" => '',
                "group_params" => '["commodities.id", "sellers.id", "farms.id"]',
                "group_by"	=>  'commodities.id, sellers.id, farms.id',
                "group_by_select"	=> 'commodities.name as commoditie_name, commodities.id as commoditie_id, sellers.name as seller_name, sellers.id as seller_id, sum(transactions_in.net) as totalnet,sum(transactions_in.netdrywt) as totalnetdrywt'
        ],
            [
                "report_name" => '',
                "field_report"	=> '',
                "select_params" => '',
                "main_table" => '',
                "inner_params" => '',
                "where_params" => '',
                "input_params" => '',
                "order_params" => '',
                "group_params" => '',
                "group_by"	=>  '',
                "group_by_select"	=> ''
        ],
            [
                "report_name" => 'REC_VOID_REPORT',
                "field_report"	=> '["DATE START", "ID", "VOID TICKET", "NAME"]',
                "select_params" => 'transactions_in.date_start, transactions_in.id, transactions_in.status, users.name, users.lastname',
                "main_table" => "transactions_in",
                "inner_params" => '{"users": ["join", "transactions_in.user", "users.id"]}',
                "where_params" => '{"transactions_in.status" : ["=" ,"9"]}',
                "input_params" => 'transactions_in.id',
                "order_params" => '',
                "group_params" => '',
                "group_by"	=>  '',
                "group_by_select"	=>  ''
        ],
            [
                "report_name" => 'SHIP_TICKET_REPORT',
                "field_report"	=> '["DATE", "TICKET#", "COMMODITIE NAME", "LIC PLATE", "LOCATION NAME",  "DRIVER NAME", "MOISTURE", "NET WT", "DRY WT"]',
                "select_params" => 'transactions_out.date_end, commodities.id as commoditie_id, commodities.name as commoditie_name, tanks.id as tank_id, tanks.name as tank_name,trucklicense, transactions_out.date_end, buyers.id as buyer_id, buyers.name as buyer_name,transactions_out.id,transactions_out.drivername,transactions_out.moisture,transactions_out.testwt,transactions_out.net,transactions_out.netdrywt, transactions_out.status,locations.id as location_id, locations.name as location_name',
                "main_table" => "transactions_out",
                "inner_params" => '{"commodities": ["join", "transactions_out.commodity", "commodities.id"],"tanks": ["join", "transactions_out.tank", "tanks.id"], "buyers": ["join", "transactions_out.buyer", "buyers.id"],"locations": ["leftjoin", "transactions_out.branch_id", "locations.id"]}',
                "where_params" => '{"transactions_out.status" : ["=" ,"2"]}',
                "input_params" => 'start_date, end_date',
                "order_params" => 'locations.name, commodities.name, buyers.name',
                "group_params" => '["commodities.id", "buyers.id"]',
                "group_by"	=>  'commodities.id, buyers.id',
                "group_by_select"	=>  'commodities.id as commoditie_id, commodities.name as commoditie_name,buyers.id as buyer_id, buyers.name as buyer_name,sum(transactions_out.netdrywt) as totalnetdrywt'
        ],
            [
                "report_name" => 'SHIP_TICKET_COMSUM',
                "field_report"	=> '["COMMODITIE NAME", "TOTAL"]',
                "select_params" => 'commodities.id as commoditie_id, commodities.name as commoditie_name',
                "main_table" => "transactions_out",
                "inner_params" => '{"commodities": ["join", "transactions_out.commodity", "commodities.id"],"tanks": ["join", "transactions_out.tank", "tanks.id"], "buyers": ["join", "transactions_out.buyer", "buyers.id"],"locations": ["leftjoin", "transactions_out.branch_id", "locations.id"]}',
                "where_params" => '{"transactions_out.status" : ["=" ,"2"]}',
                "input_params" => 'start_date, end_date',
                "order_params" => 'locations.name, commodities.name, buyers.name',
                "group_params" => '["commodities.id"]',
                "group_by"	=>  'commodities.id',
                "group_by_select"	=>  'commodities.id as commoditie_id, commodities.name as commoditie_name,sum(transactions_out.net) as net'
        ],
            [
                "report_name" => '',
                "field_report"	=> '',
                "select_params" => '',
                "main_table" => '',
                "inner_params" => '',
                "where_params" => '',
                "input_params" => '',
                "order_params" => '',
                "group_params" => '',
                "group_by"	=>  '',
                "group_by_select"	=> ''
        ],
            [
                "report_name" => 'SHIP_TICKET_REPORTBYSILO',
                "field_report"	=> '["DATE", "TICKET#", "COMMODITIE NAME", "LIC PLATE", "LOCATION NAME",  "DRIVER NAME", "MOISTURE", "NET WT", "DRY WT"]',
                "select_params" => 'commodities.id as commoditie_id, commodities.name as commoditie_name, DATE(transactions_out.date_end) as date_end, transactions_out.id,transactions_out.drivername, transactions_out.moisture,transactions_out.net, transactions_out.status,transactions_out.source_id, locations.id as location_id, locations.name as location_name, transactions_out.netdrywt, transactions_out.trucklicense',
                "main_table" => "transactions_out",
                "inner_params" => '{"commodities": ["join", "transactions_out.commodity", "commodities.id"], "locations": ["join", "transactions_out.branch_id", "locations.id"]}',
                "where_params" => '{"transactions_out.status" : ["=" ,"2"]}',
                "input_params" => '',
                "order_params" => 'locations.name,commodities.name',
                "group_params" => '["locations.id", "commodities.id", "transactions_out.date_end"]',
                "group_by"	=>  'locations.id, commodities.id, DATE(transactions_out.date_end)',
                "group_by_select"	=>  'DATE(transactions_out.date_end) as date_end, commodities.id as commoditie_id, commodities.name as commoditie_name,locations.id as location_id,locations.name as location_name, sum(transactions_out.net) as totalnet,sum(transactions_out.netdrywt) as totalnetdrywt'
        ],
            [
                "report_name" => 'SHIP_TICKET_SUMMARY',
                "field_report"	=> '["BUYER NAME","NET"]',
                "select_params" => 'commodities.id as commoditie_id, commodities.name as commoditie_name, buyers.id as buyer_id,buyers.name as buyer_name,sum(transactions_out.net) as net',
                "main_table" => "transactions_out",
                "inner_params" => '{"commodities": ["join", "transactions_out.commodity", "commodities.id"], "tanks": ["join", "transactions_out.tank", "tanks.id"], "buyers": ["join", "transactions_out.buyer", "buyers.id"]}',
                "where_params" => '{"transactions_out.status" : ["=" ,"2"]}',
                "input_params" => '',
                "order_params" => 'commodities.name, buyer.name',
                "group_params" => '["commodities.id", "buyers.id"]',
                "group_by"	=>  'commodities.id, buyers.id',
                "group_by_select"	=>  'commodities.id as commoditie_id, commodities.name as commoditie_name, sum(transactions_out.net) as net'
        ],
            [
                "report_name" => 'SHIP_VOID_REPORT',
                "field_report"	=> '["DATE START", "ID", " VOID TICKET", "NAME"]',
                "select_params" => 'transactions_out.date_start, transactions_out.id, transactions_out.status, users.name, users.lastname',
                "main_table" => "transactions_out",
                "inner_params" => '{"users": ["join", "transactions_out.user", "users.id"]}',
                "where_params" => '{"transactions_out.status" : ["=" ,"9"]}',
                "input_params" => '',
                "order_params" => 'transactions_out.id',
                "group_params" => '',
                "group_by"	=>  '',
                "group_by_select"	=> ''
        ],
            [
                "report_name" => 'CASH_TICKET_REPORT',
                "field_report"	=> '["DATE", "TICKET#", "COMMODITIE NAME", "TANK",  "DRIVER NAME", "PRICE", "WEIGHT", "TOTAL"]',
                "select_params" => 'commodities.id as commoditie_id, commodities.name as commoditie_name,tanks.id as tank_id, tanks.name as tank_name, cashsales.weight, cashsales.buyer, cashsales.price, cashsales.net, cashsales.total, cashsales.selled_at, cashsales.source_id',
                "main_table" => "cashsales",
                "inner_params" => '{"commodities": ["leftjoin", "cashsales.commodity_id", "commodities.id"],"tanks": ["leftjoin", "cashsales.tank_id", "tanks.id"]}',
                "where_params" => '',
                "input_params" => 'start_date, end_date, commodities',
                "order_params" => 'commodities.name',
                "group_params" => '["commodities.id"]',
                "group_by"	=>  'commodities.id',
                "group_by_select"	=>  'commodities.id as commoditie_id,commodities.name as commoditie_name,sum(cashsales.weight) as weight,sum(cashsales.total) as total'
        ],
            [
                "report_name" => 'TANK_REPORT_SUM',
                "field_report"	=> '["DATE", "TICKET#", "COMMODITIE NAME", "TANK",  "DRIVER NAME", "PRICE", "WEIGHT", "TOTAL"]',
                "select_params" => '',
                "main_table" => "tankstock",
                "inner_params" => '',
                "where_params" => '',
                "input_params" => '',
                "order_params" => '',
                "group_params" => '',
                "group_by"	=>  '',
                "group_by_select"	=>  ''
        ],
            [
                "report_name" => 'TANK_REPORT_RENE',
                "field_report"	=> '["LOCATION NAME", "TANK NAME", "NET LBS", "NET DRY LBS", "NET MT", "CAP MT", "BALANCE"]',
                "select_params" => '',
                "main_table" => "",
                "inner_params" => '',
                "where_params" => '',
                "input_params" => '',
                "order_params" => '',
                "group_params" => '',
                "group_by"	=>  '',
                "group_by_select"	=>  ''
        ],
            [
                "report_name" => 'STANDING_REPORT',
                "field_report"	=> '["LOCATION NAME", "COMMODITIE NAME", "NET"]',
                "select_params" => '',
                "main_table" => "",
                "inner_params" => '',
                "where_params" => '',
                "input_params" => '',
                "order_params" => '',
                "group_params" => '',
                "group_by"	=>  '',
                "group_by_select"	=>  ''
            ],
            [
                "report_name" => 'TANK_REPORT',
                "field_report"	=> '["TANK NAME", "TANK CAPACITY (bus)", "TANK STOCK (bus)", "TANK STOCK LBS", "% FULL", "COMMODITIE NAME"]',
                "select_params" => '',
                "main_table" => "",
                "inner_params" => '',
                "where_params" => '',
                "input_params" => '',
                "order_params" => '',
                "group_params" => '',
                "group_by"	=>  '',
                "group_by_select"	=>  ''
        ],
            [
                "report_name" => 'CURRENT_STOCK',
                "field_report"	=> '["COMMODITIE NAME", "CURRENTS STOCK"]',
                "select_params" => '',
                "main_table" => "",
                "inner_params" => '',
                "where_params" => '',
                "input_params" => '',
                "order_params" => '',
                "group_params" => '',
                "group_by"	=>  '',
                "group_by_select"	=>  ''
        ],
            [
                "report_name" => 'ALL_CONTRACTS',
                "field_report"	=> '["DATE", "GG TICKET", "ORG TKT", "LICENSE PLATE", "LOT NAME","NET", "DRY NET", "M %", "TERMS WT", "TERMS BAL","ASSESSMENT", "DISCOUNT", "TERMS PRICE", "TOTAL DUE"]',
                "select_params" => 'transactions_in.source_id,transactions_in.netdrywt,terms.contract_id,term_details.weight,terms.price,term_details.balance,contracts.name,terms.id as term_id,sellers.id as seller_id, sellers.name as seller_name,terms.cars,DATE(transactions_in.date_end) as date_end,transactions_in.orgticket,transactions_in.trailerlicense,transactions_in.net,commodities.name as commoditie_name,farms.name as farm_name,transactions_in.moisture,transactions_in.status,term_details.assesment,term_details.discount',
                "main_table" => "term_details",
                "inner_params" => '{"terms": ["join", "term_details.term_id", "terms.id"],"contracts": ["join", "term_details.contract_id", "contracts.id"], "transactions_in": ["join", "term_details.transaction_id", "transactions_in.id"],"sellers": ["join", "transactions_in.seller", "sellers.id"],"commodities": ["join", "transactions_in.commodity", "commodities.id"],"farms": ["join", "transactions_in.farm", "farms.id"]}',
                "where_params" => '{"transactions_in.status" : ["=" ,"2"]}',
                "input_params" => '',
                "order_params" => 'sellers.name, terms.contract_id, terms.id, transactions_in.source_id',
                "group_params" => '["sellers.name", "terms.contract_id", "terms.id", "transactions_in.source_id"]',
                "group_by"	=>  'sellers.name, terms.contract_id, terms.id, transactions_in.source_id',
                "group_by_select"	=>  'transactions_in.source_id, sellers.id as seller_id, sellers.name as seller_name, terms.contract_id, contracts.name as contract_name, terms.id as term_id,terms.cars, terms.price, sum(terms.price) as total_due'
        ],
            [
                "report_name" => 'CONTRACTS_BYFARMER',
                "field_report"	=> '["DATE", "GG TICKET", "ORG TKT", "LICENSE PLATE", "LOT NAME","NET", "DRY NET", "M %", "TERMS WT", "TERMS BAL","ASSESSMENT", "DISCOUNT", "TERMS PRICE", "TOTAL DUE"]',
                "select_params" => 'transactions_in.source_id,transactions_in.netdrywt,terms.contract_id,term_details.weight,terms.price,term_details.balance,contracts.name,terms.id as term_id,sellers.id as seller_id, sellers.name as seller_name,terms.cars,DATE(transactions_in.date_end) as date_end,transactions_in.orgticket,transactions_in.trailerlicense,transactions_in.net,commodities.name as commoditie_name,farms.name as farm_name,transactions_in.moisture,transactions_in.status,term_details.assesment,term_details.discount',
                "main_table" => "term_details",
                "inner_params" => '{"terms": ["join", "term_details.term_id", "terms.id"],"contracts": ["join", "term_details.contract_id", "contracts.id"], "transactions_in": ["join", "term_details.transaction_id", "transactions_in.id"],"sellers": ["join", "transactions_in.seller", "sellers.id"],"commodities": ["join", "transactions_in.commodity", "commodities.id"],"farms": ["join", "transactions_in.farm", "farms.id"]}',
                "where_params" => '{"transactions_in.status" : ["=" ,"2"]}',
                "input_params" => '',
                "order_params" => 'sellers.name, terms.contract_id, terms.id, transactions_in.source_id',
                "group_params" => '["sellers.id"]',
                "group_by"	=>  'sellers.name, terms.contract_id, terms.id, transactions_in.source_id',
                "group_by_select"	=>  'transactions_in.source_id, sellers.id as seller_id, sellers.name as seller_name, terms.contract_id, contracts.name as contract_name, terms.id as term_id,terms.cars, terms.price'
        ],
            [
                "report_name" => 'CHARGE_REPORT',
                "field_report"	=> '["SELLER NAME", "ELEVATOR CHARGE", "DRYING CHARGE"]',
                "select_params" => 'transactions_in.id,transactions_in.date_end,transactions_in.netdrywt,commodities.id as commoditie_id, commodities.name as commoditie_name,sellers.id as seller_id,sellers.name as seller_name,farms.id as farm_id, farms.name as farm_name,transactions_in.elevator_chr, transactions_in.drying_chr,transactions_in.status',
                "main_table" => "transactions_in",
                "inner_params" => '{"commodities": ["join", "transactions_in.commodity", "commodities.id"],"sellers": ["join", "transactions_in.seller", "sellers.id"],"farms": ["join", "transactions_in.farm", "farms.id"]}',
                "where_params" => '{"transactions_in.status" : ["=" ,"2"]}',
                "input_params" => '',
                "order_params" => 'commodities.name, sellers.name, farms.name, transactions_in.id',
                "group_params" => '["commodities.id", "sellers.id", "farms.id"]',
                "group_by"	=>  'sellers.id',
                "group_by_select"	=>  'DATE(transactions_in.date_end) as date_end,commodities.id as commoditie_id, commodities.name as commoditie_name,sellers.id as seller_id,sellers.name as seller_name,farms.id as farm_id, farms.name as farm_name,sum(transactions_in.elevator_chr) as elevator_chr,sum(transactions_in.drying_chr) as drying_chr'
        ],
            [
                "report_name" => 'TANK_MOISTURE',
                "field_report"	=> '["LOCATION NAME","TANK NAME", "COMMODITIE NAME", "AVG", "MAX", "MIN"]',
                "select_params" => 'commodities.name as commoditie_name, transactions_in.id, tanks.name as tank_name,AVG(transactions_in.moisture) AS avg_moisture, MAX(transactions_in.moisture) AS max_moisture,MIN(transactions_in.moisture) AS min_moisture,commodities.name,commodities.shrinkable,locations.name as location_name',
                "main_table" => "transactions_in",
                "inner_params" => '{"tanks": ["join", "transactions_in.tank", "tanks.id"], "commodities": ["join", "transactions_in.commodity", "commodities.id"],"locations": ["leftjoin", "transactions_in.branch_id", "locations.id"]}',
                "where_params" => '{"commodities.shrinkable" : ["=" ,"0"]}',
                "input_params" => '',
                "order_params" => 'locations.name, tanks.name',
                "group_params" => '["locations.id", "tanks.id"]',
                "group_by"	=>  'locations.name, tanks.name',
                "group_by_select"	=>  'commodities.name as commoditie_name, transactions_in.id, tanks.name as tank_name,AVG(transactions_in.moisture) AS avg_moisture, MAX(transactions_in.moisture) AS max_moisture,MIN(transactions_in.moisture) AS min_moisture,commodities.name,commodities.shrinkable,locations.name as location_name'
        ]
        ]);
    }
}
