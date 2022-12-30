<?php

namespace App\Http\Requests;

use App\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class CreateUserRequest extends FormRequest
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
            'name' => 'required',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:7',
            'bio' => 'required',
            'twitter' => ['nullable', 'url'],
            'profession_id' => [Rule::exists('professions', 'id')->where('selectable', true), Rule::exists('professions', 'id')->whereNull('deleted_at')]

        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'El nombre es obligatorio',
            'email.required' => 'El correo electrónico es obligatorio',
            'email.unique' => 'El correo electrónico debe ser único',
            'email.email' => 'El correo electrónico debe ser válido',
            'password.required' => 'La contraseña es obligatoria',
            'password.min' => 'La contraseña debe tener mas de seis caracteres',
            'bio.required' => 'La biografia es obligatoria',
            'twitter.url' => 'El twitter debe ser una url',
            'profession_id.exists' => 'La profesión debe ser válida',
        ];
    }

    public function createUser()
    {
        DB::transaction(function() {

            $data = $this->validated();

            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => bcrypt($data['password']),
                'profession_id' => $data['profession_id'] ?? null,
            ]);

            $user->profile()->create([
                'bio' => $data['bio'],
                'twitter' => $data['twitter'] ?? null,
            ]);
        });
    }
}