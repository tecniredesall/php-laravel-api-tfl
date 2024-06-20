<?php

namespace App;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Database\Eloquent\Model;

class NumberFormat extends Model
{
    public static function simpleFormatGeneral($value){
        try{
            $numberFormat = new NumberFormat;
            $general = $numberFormat->getDBValues();
            if(rtrim($value) === "" ){
                $format = number_format(0, $general->decimals_in_general);
            }elseif(isset($value) and rtrim($value) !== "") {
                $format = number_format($value, $general->decimals_in_general);
            }else{
                $format = number_format(0, $general->decimals_in_general);
            }
            return $format;
        }catch ( \Exception $e ){
            return $e->getMessage();
        }
    }

    public static function defaultFormat($value){
        try {
            if(rtrim($value) == "" ){
                $format = number_format(0, 0);
            }elseif(isset($value) and rtrim($value) !== "") {
                $format = number_format($value, 0);
            }else{
                $format = number_format(0, 0);
            }
            return $format;
        }catch ( \Exception $e ){
            return $e->getMessage();
        }
    }

    public static function totalFormat($value){
        try{
            $format = number_format($value, 2);
            return $format;
        }catch ( \Exception $e ){
            return $e->getMessage();
        }
    }

    protected static function numberFormatGeneral($number, $dec_point, $thousands_sep)
    {
        $was_neg = $number < 0;
        $number = abs($number);
        $tmp = explode('.', $number);
        $numberFormat = new NumberFormat;
        $general = $numberFormat->getDBValues();
        $out = number_format($tmp[0], $general->decimals_in_general, $dec_point, $thousands_sep);
        if (isset($tmp[1]))
            $out .= $dec_point . $tmp[1];
        if ($was_neg)
            $out = "-$out";
        return $out;
    }

    public static function simpleFormatTickets($value){
        try{
            $numberFormat = new NumberFormat;
            $tickets = $numberFormat->getDBValues();
            if(rtrim($value) == "" ){
                $format = number_format(0, $tickets->decimals_in_tickets);
            }elseif(isset($value) and rtrim($value) !== "") {
                $format = number_format($value, $tickets->decimals_in_tickets);
            }else{
                $format = number_format(0, $tickets->decimals_in_tickets);
            }
            return $format;
        }catch ( \Exception $e ){
            return $e->getMessage();
        }
    }

    public static function totalFormatTickets($value){
        try{
            $format = number_format($value, 4);

            return $format;
        }catch ( \Exception $e ){
            return $e->getMessage();
        }
    }

    public static function formatCapacityGeneral($value, $capacity ){
        try{
            $numberFormat = new NumberFormat;
            $general = $numberFormat->getDBValues();
            $percent = (double)number_format(($value * 100) / $capacity, $general->decimals_in_general);
            return $percent;
        }catch ( \Exception $e ){
            return $e->getMessage();
        }
    }

    public static function formatCapacityPercent($value, $capacity ){
        try{
            $percent = (double)number_format(($value * 100) / $capacity, 2);
            return $percent;
        }catch ( \Exception $e ){
            return $e->getMessage();
        }
    }

    public static function hardFormat($value){
        try{
            $percent = number_format($value,3);

            return $percent;
        }catch ( \Exception $e ){
            return $e->getMessage();
        }
    }

    public static function formatPercent($value){
        try{
            $percent = number_format($value,2) . '%';

            return $percent;
        }catch ( \Exception $e ){
            return $e->getMessage();
        }
    }

    protected static function tonsFormatGeneral($value){
        try{
            $numberFormat = new NumberFormat;
            $money = $numberFormat->getDBValues();
            if( $money->metric_system_id == 1 ) {
                $tons = number_format($value / 2204.6226218, $money->decimals_in_general);
            }else{
                $tons = number_format($value / 2204.6226218, $money->decimals_in_general);
                //$tons = number_format($value / 1000, $money->decimals_in_general);
            }

            return $tons;
        }catch ( \Exception $e ){
            return $e->getMessage();
        }
    }

    protected static function tonsFormatTickets($value){
        try{
            $numberFormat = new NumberFormat;
            $money = $numberFormat->getDBValues();
            if( $money->metric_system_id == 1 ) {
               $tons = number_format($value / 2204.6226218, $money->decimals_in_tickets);
            }else{
                $tons = number_format($value / 2204.6226218, $money->decimals_in_tickets);
               //$tons = number_format($value / 1000, $money->decimals_in_tickets);
            }

            return $tons;
        }catch ( \Exception $e ){
            return $e->getMessage();
        }
    }

    protected static function bushelFormat($value){
        try{
            $numberFormat = new NumberFormat;
            $money = $numberFormat->getDBValues();

            if( $money->metric_system_id == 1 ){
                $bushel = number_format($value / 56, $money->decimals_in_tickets);
            }else{
                $bushel = number_format($value / 56, $money->decimals_in_tickets);
               //$bushel = number_format($value / 25.4, $money->decimals_in_tickets);
            }
            return $bushel;
        }catch ( \Exception $e ){
            return $e->getMessage();
        }
    }

    protected static function bushelFormatGeneral($value){
        try{
            $numberFormat = new NumberFormat;
            $money = $numberFormat->getDBValues();

            if( $money->metric_system_id == 1 ){
                $bushel = number_format($value / 56, 2);
            }else{
                $bushel = number_format($value / 56, 2);
                //$bushel = number_format($value / 25.4, 2);
            }
            return $bushel;
        }catch ( \Exception $e ){
            return $e->getMessage();
        }
    }



    public static function simpleFormatReports($value){
        try{
            $numberFormat = new NumberFormat;
            $tickets = $numberFormat->getDBValues();
            if(rtrim($value) == "" ){
                $format = number_format(0, $tickets->decimals_in_tickets);
            }elseif(isset($value) and rtrim($value) !== "") {
                $format = number_format($value, $tickets->decimals_in_tickets);
            }else{
                $format = number_format(0, $tickets->decimals_in_tickets);
            }
            return $format;
        }catch ( \Exception $e ){
            return $e->getMessage();
        }
    }

    public static function totalGeneralFormatReports($value){
        try{
            $format = number_format($value, 2);

            return $format;
        }catch ( \Exception $e ){
            return $e->getMessage();
        }
    }

    public static function totalLongFormatReports($grant, $long){
        try{
            $format = number_format($grant/$long, 2);

            return $format;
        }catch ( \Exception $e ){
            return $e->getMessage();
        }

    }

    public static function capacityPercent($stocklb, $capacity){
        try{
            $percent = ( $capacity !== 0  ) ? ($stocklb * 100) / $capacity : 0;
            return $percent;
        }catch ( \Exception $e ){
            return $e->getMessage();
        }
    }

    private function getDBValues(){
        $info = \App\Company_info::selectRaw('metric_system_id, decimals_in_tickets, decimals_in_general, decimals_for_money')->first();
        return $info;
    }

}
