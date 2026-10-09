<?php

namespace Database\Factories;

use App\Models\Ucionica;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class TerminFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'ucionica_id' => Ucionica::factory(),
            'user_id' => User::factory(),
            'created_by' => User::factory(),
            'updated_by' => User::factory(),
            'naziv' => fake()->word(),
            'opis' => fake()->text(),
            'datum' => fake()->date(),
            'vreme_pocetka' => fake()->time(),
            'vreme_kraja' => fake()->time(),
            'bulk_uid' => fake()->word(),
            'status' => fake()->randomElement(["potvrdjen","odbijen","ceka"]),
        ];
    }
}
