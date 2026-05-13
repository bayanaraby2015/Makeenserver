<?php

namespace Database\Factories;

use App\Models\Consultation;
use App\Models\Initiative;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Consultation>
 */
class ConsultationFactory extends Factory
{
    protected $model = Consultation::class;

    public function definition(): array
    {
        return [
            'requester_organization_id' => Organization::factory(),
            'initiative_id' => Initiative::factory(),
            'consultant_user_id' => User::factory(),
            'specialization' => $this->faker->randomElement(['financial', 'operational', 'impact']),
            'subject' => $this->faker->sentence(4),
            'details' => $this->faker->paragraph(),
            'status' => 'requested',
            'requested_at' => now(),
        ];
    }
}

