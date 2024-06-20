<?php

namespace App;

use App\Helpers\LanguageHelper;
use App\Mail\MailNewUser;
use App\Mail\MailPassword;
use Illuminate\Http\JsonResponse;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class Api extends Model
{


    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * searchIsMatchEmail
     *
     * @return bool
     * @var type
     * @var email
     */
    protected static function searchIsMatchEmail($email, $type)
    {
        $model = '';
        if ($type == 'users')
            $model = '\App\Users';
        else
            $model = '\App\Sellers';
        try {
            $model::where('email', $email)->firstOrFail();
            return true;
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return false;
        }
    }

    /**
     * sendResetPass
     *
     * @param $id
     * @param $model
     * @param $f
     * @param $email
     * @param string $lang
     * @param int $template
     * 1: Reset
     * 2: New Silosys user
     * 3: New Silosys farm user
     * @return JsonResponse|string
     */
    protected static function sendResetPass($id, $model, $f, $email, $lang = 'en', $template = "RESET_PASSWORD")
    {
        try {
            $langHelper = new LanguageHelper();
            $lang = $langHelper->Normalize($lang);
            $resetPassword = new ResetPassword();
            $result = $resetPassword->getResetLink($id, $model, $f, $email, $lang);
            if ($result["success"]) {
                if ($f == 1) {
                    Log::debug($result["email"]);
                    Mail::to($result["email"])
                        ->send(new MailPassword($lang, $result["name"], $result["link"], $template));

                    $location_id = \App\Company_info::pluck('default_location')[0];
                    Log::debug("send data harvx");
                    $json = array();
                    $data = array();
                    $data["email"] = $result["email"];
                    $data["idInstance"] = $location_id;
                    $data["type"] = ($model == 'Users') ? 'owner' : 'farmer';
                    $json["payload"] = $data;
                    $json["event"] = "password-changed";

                    $array = [
                        'group' => env('INSTANCE_ID'),
                        'type' => 'REQUEST',
                        'action' => 'UpdatePassword',
                        'destination' => $location_id,
                        'message' => json_encode($json)
                    ];

                    \App\SQS::send($array, 'local', $location_id, 'harvx');

                    return response()->json([
                        'status' => true,
                        'code' => 200,
                        'message' => 'Sent email has been successfully'
                    ], 200);
                } else {
                    return $result["link"];
                }
            } else {
                Log::error($result["error"]);
                return response()->json([
                    'status' => false,
                    'code' => 404,
                    'message' => $result["error"]
                ], 404);
            }
        } catch (\Exception $e) {
            Log::error($e->getMessage());
        }
    }

    /**
     * get pagination
     *
     * @return json
     * @var model
     */
    protected static function getPaginator($model, $total = 0, $request = array())
    {
        $page = isset($request->page) ? $request->page : 1; // get current page or default to 1
        $limit = isset($request->limiter) ? $request->limiter : env('PER_PAGE');
        $limit = intval($limit);
        $offset = ($page * $limit) - $limit;
        $items = $model;
        if (($request !== false) && count($request->all()) > 0) {
            return new LengthAwarePaginator(
                ($total == 0) ? array_slice($items, $offset, $limit, false) : $items,
                ($total == 0) ? count($items) : $total,
                $limit,
                $page,
                [
                    'path' => \URL::current(),
                    'query' => $request->query()
                ]
            );
        } else {
            return new LengthAwarePaginator(
                ($total == 0) ? array_slice($items, $offset, $limit, false) : $items,
                ($total == 0) ? count($items) : $total,
                $limit,
                $page,
                [
                    'path' => \URL::current(),
                    // 'query' => $request->query()
                ]
            );
        }
    }

    /**
     * Response HTTP Status
     *
     * @var bool
     */
    protected static function my_number_format($number, $dec_point, $thousands_sep)
    {
        $was_neg = $number < 0; // Because +0 == -0
        $number = abs($number);
        $tmp = explode('.', $number);
        $out = number_format($tmp[0], 0, $dec_point, $thousands_sep);
        if (isset($tmp[1]))
            $out .= $dec_point . $tmp[1];
        if ($was_neg)
            $out = "-$out";
        return $out;
    }

    /**
     * get Time
     *
     * @return string|json
     * @var filter
     * @var id
     */
    protected static function getTime($id, $filter)
    {
        if ($filter == 0) {
            $query = \App\TransactionsIn::where('branch_id', $id)
                ->where('status', '>', 0)
                ->where('status', '<', 4)
                ->where('date_end', '<=', \Carbon\Carbon::now())
                ->selectRaw('date_start AS start, date_end AS end');
        } else {
            $query = \App\TransactionsOut::where('branch_id', $id)
                ->where('status', '>', 0)
                ->where('status', '<', 4)
                ->where('date_end', '<=', \Carbon\Carbon::now())
                ->selectRaw('date_start AS start, date_end AS end');
        }
        $limit = env('ITEMS_REMOVE_UP_TO_DOWN') * 3;
        if ($query->count() >= $limit) {
            $array = array();
            foreach ($query->limit($limit)->get() as $key => $val) {
                $start = new \Carbon\Carbon($val->start);
                $end = new \Carbon\Carbon($val->end);
                $array[] = $end->diffInRealSeconds($start);
            }
            $array = array_slice($array, env('ITEMS_REMOVE_UP_TO_DOWN'), (-1 * abs(env('ITEMS_REMOVE_UP_TO_DOWN'))));
            $averange = array_sum($array) / count($array);
            return static::getFormatDates($averange);
        } else
            return 'N/A';
    }

    /**
     * getCommoditiesArray
     *
     * @return array
     * @var filter
     * @var query
     * @var array
     * @var id
     */
    protected static function getCommoditiesArray($id, $filter, $query, $array)
    {
        $rows = array();
        foreach ($query->get() as $key => $val)
            if (array_key_exists($val->commodity, $array))
                $array[$val->commodity]['total'] = $array[$val->commodity]['total'] + 1;
        foreach ($array as $key => $val)
            if ($val['total'] != 0)
                $rows[] = $val;
        return $rows;
    }

    /**
     * getCommoditiesArray
     *
     * @return bool
     * @var idPermission
     * @var id
     */
    protected static function iCan($id, $idPermission)
    {
        $array = array();
        $obj = \App\Users::with(array('security'))->find($id)->security->toArray();
        if (count($obj) > 0) {
            foreach ($obj as $key => $val)
                $array[] = $val['id'];
            if (in_array(1, $array))
                return true;
            else
                if (in_array($idPermission, $array))
                    return true;
                else
                    return false;
        } else
            return false;
    }

    /**
     * getFormatDates
     *
     * @return string
     * @var value
     */
    protected static function getFormatDates($value)
    {
        $return = '';
        $s = $value % 60;
        $d = floor(($value % 2592000) / 86400);
        if ($d >= 1)
            $return .= $d . 'd ';
        $h = floor(($value % 86400) / 3600);
        if ($h >= 1)
            $return .= $h . 'h ';
        $m = floor(($value % 3600) / 60);
        if ($m >= 1)
            $return .= $m . 'm ';
        $return .= number_format($s, 0) . 's';
        return $return;
    }

}
