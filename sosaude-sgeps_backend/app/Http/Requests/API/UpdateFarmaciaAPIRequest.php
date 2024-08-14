<?php

namespace App\Http\Requests\API;

use App\Models\Farmacia;
use InfyOm\Generator\Request\APIRequest;

class UpdateFarmaciaAPIRequest extends APIRequest
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
        $id = $this->route('id');
        $rules_update = ['nome' => 'unique:farmacias,nome,'.$id];
        $rules = Farmacia::$rules;
        
        return array_merge($rules, $rules_update);
    }
}
