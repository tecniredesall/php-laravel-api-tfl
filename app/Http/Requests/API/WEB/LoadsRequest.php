<?php

namespace App\Http\Requests\API\WEB;

use App\Models\CmodityLoads;
use App\TransactionsIn;
use Illuminate\Foundation\Http\FormRequest;

class LoadsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            //
        ];
    }
    public  function updateLoads(){
        $loadsId=$this->input('loads_id');
        foreach ($loadsId as $loadId){
            $load= CmodityLoads::find($loadId);
            if($load){
                $load->was_processed=1;
                $load->process_date= now();
                $load->save();
            }
        }

        return $loadsId;
    }
    public function getAllLoads(){
        $loads=CmodityLoads::selectRaw("*");
        if ($this->has('processed'))
            $loads=$loads->where('was_processed', intval($this->input('processed')));

        if($this->has('had_error'))
            $loads=$loads->where('had_error', intval($this->input('had_error')));
        return $loads->paginate($this->input("limit",10));
    }
}
