<?php

namespace App\Http\Controllers\API\WEB;

use Illuminate\Http\Request;
use App\Http\Controllers\API\BaseController as BaseController;
use Illuminate\Support\Facades\Log;

class CommoditiesGeneral extends BaseController
{
    private $permission = 4;

    /**
     * Enable this module.
     *
     */
    public function __construct()
    {
        $this->middleware('candoit:' . $this->permission);
    }

    /**
     * Display a listing of the resource.
     *
     */
    public function getCommoditiesGeneral(Request $request)
    {
        try {
            $page = $request->page ? $request->page : 1;
            $size = $request->size ? $request->size : 10;
            $lang = $request->language ? $request->language : 'en';
            return $this->sendSuccess('', array('data' => \App\CommoditiesGeneral::getCommoditiesGeneral($page, $size, $lang, $request)));
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return $this->sendError( 'Internal Server Error', array(), 500 );
        }
    }

    public function updateCommoditiesGeneral(Request $request)
    {
        dd("elluisillo");
        try {
            $lang = $request->language ? $request->language : 'en';
            $isUpdated = \App\CommoditiesGeneral::updateCommoditiesGeneral($lang);
            if ($isUpdated) {
                return $this->sendSuccess('', array('data' => \App\CommoditiesGeneral::where('available', 1)->get()));
            }
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return $this->sendError( 'Internal Server Error', array(), 500 );
        }
    }
    /**
     * Display a listing of the resource.
     *
     */
    public function listCommoditiesGeneral(Request $request)
    {
        try {
            return $this->sendSuccess('', array('data' => \App\CommoditiesGeneral::where('available', 1)->get()));
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return $this->sendError( 'Internal Server Error', array(), 500 );
        }
    }

}
