<?php

namespace Database\Factories;

use App\Models\EventShare;
use App\Models\GroupEvent;
use App\Models\Member;
use App\Models\Group;
use Illuminate\Database\Eloquent\Factories\Factory;

class EventShareFactory extends Factory
{
    protected $model = EventShare::class;

    public function definition()
    {
        return [
            'group_event_id' => GroupEvent::factory(),
            'share_type' => $this->faker->randomElement(['group', 'member', 'email']),
            'shared_with_group_id' => null,
            'shared_with_member_id' => null,
            'shared_with_email' => null,
            'shared_by' => Member::factory(),
            'message' => $this->faker->sentence(),
            'token' => $this->faker->uuid(),
            'status' => 'pending',
            'expires_at' => $this->faker->dateTimeBetween('now', '+7 days'),
            'accepted_at' => null,
            'declined_at' => null,
        ];
    }

    public function forGroup()
    {
        return $this->state(function (array $attributes) {
            return [
                'share_type' => 'group',
                'shared_with_group_id' => Group::factory(),
                'shared_with_member_id' => null,
                'shared_with_email' => null,
            ];
        });
    }

    public function forMember()
    {
        return $this->state(function (array $attributes) {
            return [
                'share_type' => 'member',
                'shared_with_group_id' => null,
                'shared_with_member_id' => Member::factory(),
                'shared_with_email' => null,
            ];
        });
    }

    public function forEmail()
    {
        return $this->state(function (array $attributes) {
            return [
                'share_type' => 'email',
                'shared_with_group_id' => null,
                'shared_with_member_id' => null,
                'shared_with_email' => $this->faker->email(),
            ];
        });
    }
}

