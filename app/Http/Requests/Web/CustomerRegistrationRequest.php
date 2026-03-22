<?php

namespace App\Http\Requests\Web;

use App\Traits\CalculatorTrait;
use App\Traits\RecaptchaTrait;
use App\Traits\ResponseHandler;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Validator;

class CustomerRegistrationRequest extends FormRequest
{
    use CalculatorTrait, ResponseHandler;
    use RecaptchaTrait;

    protected $stopOnFirstFailure = true;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'f_name' => 'required',
            'email' => 'required|email|unique:users',
            'phone' => 'required|unique:users|max:20',
            'password' => [
                'required',
                'min:8',
                'same:con_password',
                // At least: 1 uppercase, 1 lowercase, 1 digit, 1 special character (all special chars allowed, no spaces)
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^a-zA-Z0-9\s])(?!.*\s).{8,}$/',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'f_name.required' => translate('first_name_is_required'),
            'email.unique' => translate('email_already_has_been_taken'),
            'phone.required' => translate('phone_number_is_required'),
            'phone.unique' => translate('phone_number_already_has_been_taken'),
            'phone.max' => translate('The_phone_number_may_not_be_greater_than_20_characters'),
            'password.min' => translate('Password_must_be_at_least_8_characters'),
            'password.regex' => translate('Password_must_contain_uppercase_lowercase_number_and_special_character'),
            'password.same' => translate('passwords_must_match_with_confirm_password'),
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator) {

                $numericPhoneValue = preg_replace('/[^0-9]/', '', $this['phone']);
                $numericLength = strlen($numericPhoneValue);
                if ($numericLength < 4) {
                    $validator->errors()->add(
                        'phone.min',
                        translate('The_phone_number_must_be_at_least_4_characters')
                    );
                }

                if ($numericLength > 20) {
                    $validator->errors()->add(
                        'phone.max',
                        translate('The_phone_number_may_not_be_greater_than_20_characters')
                    );
                }
            },
        ];
    }

    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        throw new HttpResponseException(response()->json(['errors' => $this->errorProcessor($validator)]));
    }
}
