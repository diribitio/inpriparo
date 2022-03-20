<?php

namespace App\Actions\Fortify;

use App\Models\ApplicationSettings;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param  array  $input
     * @return \App\Models\User
     */
    public function create(array $input)
    {
        Validator::make($input, [
            'name' => ['required', 'string', 'min:3', 'max:255', Rule::unique(User::class),],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                'not_regex:/[^\x20-\x7e]/', // TODO Might be removed but mailtrap.io can't handle special chars in the email address
                Rule::unique(User::class),
            ],
            'password' => $this->passwordRules(),
        ])->validate();


            $user = User::create([
            'name' => $input['name'],
            'email' => $input['email'],
            'password' => Hash::make($input['password']),
        ]);

        $appSettings = ApplicationSettings::take(1)->first();

        $admin_email_parts = explode('@', $user->email);
        $admin_email_domain = end($admin_email_parts);

        if ('@' . $admin_email_domain == $appSettings->non_guest_email_domain) {
            $user->syncRoles(['user', 'attendant']);
        } else {
            $user->syncRoles(['user', 'guestAttendant']);
        }

        return $user;
    }
}
