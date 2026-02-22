<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;
use Illuminate\Validation\Rule;

class RawItemRequest extends FormRequest
{
    /**
     * Indicates if the validator should stop on the first rule failure.
     *
     * @var bool
     */
    protected $stopOnFirstFailure = true;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {

        $rulesArray = [
            'item_category_id'                => ['required'],
            'price'                           => ['required', 'numeric'],
            'tax_id'                          => ['required', 'numeric'],
            'base_unit_id'                    => ['required'],
            'description'                     => ['nullable','string', 'max:250'],
            'status'                          => ['required'],
            'opening_quantity'                => ['nullable'],
        ];

        if ($this->isMethod('PUT')) {
            $itemId                     = $this->input('item_id');
            $rulesArray['name']          = ['required', 'string', 'max:100', (app('company')['is_item_name_unique']) ? Rule::unique('raw_items')->where('name', $_POST['name'])->ignore($itemId) : null];
            $rulesArray['item_code']     = ['required', 'string', 'max:100', Rule::unique('raw_items')->where('item_code', $_POST['item_code'])->ignore($itemId)];
        }else{
            $rulesArray['name']          = ['required', 'string', 'max:100', (app('company')['is_item_name_unique']) ? Rule::unique('raw_items')->where('name', $_POST['name']) : null];
            $rulesArray['item_code']     = ['required', 'string', 'max:100', Rule::unique('raw_items')->where('item_code', $_POST['item_code'])];
        }

        return $rulesArray;

    }
    public function messages(): array
    {
        $responseMessages = [];

        if ($this->isMethod('PUT')) {
            $responseMessages['item_id.required']    = 'ID Not found to update record';
        }

        return $responseMessages;
    }
    /**
     * Get the "after" validation callables for the request.
     */
    public function withValidator(Validator $validator)
    {
        $validator->after(function ($validator) {
            $data = $validator->getData();
            $data['price']             = $data['price']??0;
            $data['opening_quantity']  = $data['opening_quantity']??0;

            $this->replace($data);
        });
    }
}
