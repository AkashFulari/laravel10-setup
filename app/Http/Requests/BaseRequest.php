<?php

namespace App\Http\Requests;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class BaseRequest extends FormRequest
{
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
        return [
            //
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        if ($this->expectsJson()) {
            $error = $validator->errors()->first();
            throw new HttpResponseException(
                response()->json(['message' => $error, 'status' => false], 422)
            );
        }

        parent::failedValidation($validator);
    }

    protected function failedAuthorization()
    {
        $request = request()->method();
        // throw new AuthorizationException("Un-Authorised " . $request . " action.");
        throw new AuthorizationException("Un-Authorised action.");
    }
}
