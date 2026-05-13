<?php

namespace Database\Factories;

use App\Models\Initiative;
use App\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Initiative>
 */
class InitiativeFactory extends Factory
{
    protected $model = Initiative::class;

    public function definition(): array
    {
        $start = $this->faker->dateTimeBetween('+1 week', '+1 month');
        $end = (clone $start)->modify('+12 weeks');

        return [
            'organization_id' => Organization::factory(),
            'name_ar' => 'مبادرة '.$this->faker->unique()->numerify('###'),
            'name_en' => 'Initiative '.$this->faker->unique()->numerify('###'),
            'domain' => $this->faker->randomElement([
                'developmental_impact',
                'sustainability',
                'institutional_empowerment',
            ]),
            'related_criteria' => 'منهجية تصميم البرامج والمنتجات',
            'development_justification' => $this->faker->sentence(8),
            'main_goal' => $this->faker->sentence(10),
            'description' => $this->faker->paragraph(3),
            'strategic_objectives' => $this->faker->sentence(6),
            'responsible_department' => 'إدارة البرامج',
            'owner_name' => $this->faker->name(),
            'partners' => null,
            'beneficiaries_scope' => $this->faker->numberBetween(50, 500).' مستفيد',
            'duration_weeks' => 12,
            'start_date' => $start,
            'end_date' => $end,
            'total_cost' => 100000.00,
            'vat_amount' => 15000.00,
            'grand_total' => 115000.00,
            'currency' => 'SAR',
            'status' => 'draft',
        ];
    }

    public function submitted(): self
    {
        return $this->state(fn () => [
            'status' => 'submitted',
            'submitted_at' => now(),
        ]);
    }

    public function approved(): self
    {
        return $this->state(fn () => [
            'status' => 'approved',
            'submitted_at' => now()->subDays(7),
            'approved_at' => now(),
        ]);
    }

    public function rejected(): self
    {
        return $this->state(fn () => [
            'status' => 'rejected',
            'submitted_at' => now()->subDays(7),
            'rejected_at' => now(),
            'rejection_reason' => 'بحاجة لمزيد من التفاصيل في القسم المالي.',
        ]);
    }
}
