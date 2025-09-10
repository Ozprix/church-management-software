<?php

namespace Database\Factories;

use App\Models\Event;
use App\Models\EventCategory;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class EventFactory extends Factory
{
    protected $model = Event::class;

    public function definition()
    {
        $startDate = $this->faker->dateTimeBetween('now', '+1 year');
        
        return [
            'title' => $this->faker->sentence,
            'description' => $this->faker->paragraph,
            'start_date' => $startDate,
            'end_date' => $this->faker->dateTimeBetween($startDate, '+1 week'),
            'location' => $this->faker->address,
            'category_id' => EventCategory::factory(),
            'created_by' => User::factory(),
            'is_recurring' => $this->faker->boolean(20),
            'is_public' => $this->faker->boolean(80),
            'capacity' => $this->faker->numberBetween(10, 100),
        ];
    }
}