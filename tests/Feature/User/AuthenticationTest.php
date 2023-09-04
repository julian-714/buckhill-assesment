<?php

namespace Tests\Feature\User\AuthenticationTest;

use App\Models\User;
use Tests\TestCase;
use Illuminate\Support\Str;

class AuthenticationTest extends TestCase
{
    /** User registration validation test case */
    public function testRequiredFieldsForRegistration()
    {
        $userData = [
            'uuid' => '',
            'first_name' => '',
            'last_name' => '',
            'email' => 'eedd',
            'password' => '',
            'password_confirmation' => '',
            'avatar' => '',
            'address' => '',
            'phone_number' => '',
            'is_marketing' => '',
        ];

        $reqValidate = $this->post('api/v1/user/register', $userData);

        $reqValidate->assertStatus(200);

        $jsonData = $reqValidate->json();

        $this->assertArrayHasKey('data', $jsonData);
        $this->assertNotEmpty($jsonData['data']);
    }

    /** User registration test case */
    public function testSuccessfulRegistration()
    {
        $userData = [
            'uuid' => Str::orderedUuid(),
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'email' => fake()->unique()->safeEmail(),
            'password' => 'userpassword',
            'password_confirmation' => 'userpassword',
            'avatar' => Str::orderedUuid(),
            'address' => fake()->address(),
            'phone_number' => fake()->numerify('##########'),
            'is_marketing' => 1,
        ];

        $reqRegister = $this->json('POST', 'api/v1/user/register', $userData)
            ->assertStatus(200);

        $jsonData = $reqRegister->json();
        $this->assertArrayHasKey('data', $jsonData);
        $this->assertNotEmpty($jsonData['data']);
    }

    /** User login test case */
    public function testSuccessfulLogin()
    {
        $userEmail = User::where('is_admin', 0)->first();

        $userData = [
            'email' => $userEmail->email,
            'password' => 'userpassword',
        ];

        $reqLogin = $this->json('POST', 'api/v1/user/login', $userData)
            ->assertStatus(200);
        $jsonData = $reqLogin->json();
        $this->assertArrayHasKey('data', $jsonData);
        $this->assertNotEmpty($jsonData['data']);
    }
}
