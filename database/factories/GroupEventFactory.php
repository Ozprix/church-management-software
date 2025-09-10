<?php

namespace Database\Factories;

use App\Models\GroupEvent;
use App\Models\Group;
use Illuminate\Database\Eloquent\Factories\Factory;

class GroupEventFactory extends Factory
{
    protected $model = GroupEvent::class;

    public function definition()
    {
        $eventDate = $this->faker->dateTimeBetween('now', '+1 year');
        
        return [
            'group_id' => Group::factory(),
            'title' => $this->faker->sentence(3),
            'description' => $this->faker->paragraph(),
            'event_date' => $eventDate->format('Y-m-d'),
            'start_time' => $this->faker->time('H:i'),
            'end_time' => $this->faker->time('H:i'),
            'location' => $this->faker->address(),
            'event_type' => $this->faker->randomElement(['meeting', 'service', 'social', 'educational', 'outreach']),
            'is_recurring' => $this->faker->boolean(20),
            'notify_members' => $this->faker->boolean(80),
            'is_active' => true,
            'registration_required' => $this->faker->boolean(30),
            'registration_capacity' => $this->faker->numberBetween(10, 100),
            'allow_guests' => $this->faker->boolean(60),
            'max_guests_per_registration' => $this->faker->numberBetween(1, 5),
            'send_reminders' => $this->faker->boolean(70),
            'is_shareable' => $this->faker->boolean(80),
            'is_public' => $this->faker->boolean(60),
            'view_count' => $this->faker->numberBetween(0, 100),
            'registration_count' => $this->faker->numberBetween(0, 50),
            'attendance_count' => $this->faker->numberBetween(0, 50),
        ];
    }
}

