<?php

namespace App\Http\Requests\API\WEB;

use Illuminate\Foundation\Http\FormRequest;

class DropboxSyncRequest extends FormRequest
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

    public function solveChallenge()
    {

    }
}
