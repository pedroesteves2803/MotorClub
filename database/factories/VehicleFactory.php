<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class VehicleFactory extends Factory
{
    public function definition(): array
    {
        return ['user_id' => User::factory(), 'vehicle_type' => 'car', 'brand' => 'Volkswagen', 'model' => 'Golf', 'public_name' => null, 'slug' => null, 'trim' => 'GTI', 'generation' => null, 'color' => null, 'model_year' => 2017, 'engine' => '2.0 TSI', 'mileage' => null, 'mileage_approximate' => false, 'acquired_on' => null, 'location' => null, 'instagram_url' => null, 'visibility' => 'private'];
    }
}
