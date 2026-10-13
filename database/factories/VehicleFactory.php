<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class VehicleFactory extends Factory
{
    public function definition(): array
    {
        return ['user_id' => User::factory(), 'brand' => 'Volkswagen', 'model' => 'Golf', 'trim' => 'GTI', 'model_year' => 2017, 'engine' => '2.0 TSI', 'visibility' => 'private'];
    }
}
