<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;

class ClientFactory extends Factory
{
    protected $model = \App\Models\Client::class;

    public function definition()
    {
        return [
            'user_id' => User::factory(), // new user create karega aur id assign karega
            'location_id' => 1,           // ya koi default location id
            'date_of_birth' => $this->faker->date(),
            'phone' => $this->faker->phoneNumber(),
            'medical_history' => $this->faker->sentence(),
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
        ];
    }
}
