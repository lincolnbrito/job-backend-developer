<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->company,
            'price' => $this->faker->randomFloat(2, 1, 999),
            'description' => $this->faker->text(150),
            'category' => $this->faker->word,
            'image_url' => $this->faker->imageUrl()
        ];
    }
}
