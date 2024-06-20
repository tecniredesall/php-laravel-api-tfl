<?php


namespace App;


use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Log;

class ResetPassword
{
    protected $appUrl;

    protected $uriSilosys;

    protected $instanceId;

    public function __construct()
    {
        $this->appUrl = env('APP_URL');
        $this->uriSilosys = env('URI_SILOSYS');
        $this->instanceId = env('INSTANCE_ID');
    }

    public function getResetLink($id, $modelName, $f, $email, $lang = 'en')
    {
        try {
            $model = $this->getModel($modelName);
            $obj = ( $email == '' && $id != 0 ) ? $model::where('id', $id)->firstOrFail() : $model::where('email', $email)->firstOrFail();
            $obj[ 'instance_id' ] = $this->instanceId;
            $uri = urlencode($this->appUrl . '/api/reset');
            $link = url($this->uriSilosys . '/reset?hash=' . encrypt($obj->tojson()) . '&h=' . encrypt($modelName) . '&i=' . $uri);

            return array("success" => true,
                "name" => $obj->name,
                "email" => $obj->email,
                "link" => $link);
        } catch (ModelNotFoundException $e) {
            Log::error($e);
            return array("success" => false, "error" => $e->getMessage());
        }catch(\Exception $e){
            Log::error($e);
            return array("success" => false, "error" => $e->getMessage());
        }
    }

    private function getModel($model){
        return str_replace(" ", "", str_replace("'", "\ ", "\App'" . $model));
    }
}