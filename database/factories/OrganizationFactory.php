<?php

namespace Database\Factories;

use App\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Organization>
 */
class OrganizationFactory extends Factory
{
    protected $model = Organization::class;

    public function definition(): array
    {
        return [
            'type' => 'association',
            'name_ar' => 'جمعية '.$this->faker->unique()->words(2, true),
            'name_en' => 'Association '.$this->faker->unique()->words(2, true),
            'license_number' => (string) $this->faker->unique()->numberBetween(1000, 99999),
            'license_authority' => 'وزارة الموارد البشرية',
            'city' => 'الرياض',
            'address' => $this->faker->streetAddress(),
            'phone' => '0500000'.$this->faker->unique()->numberBetween(100, 999),
            'email' => $this->faker->unique()->safeEmail(),
            'website' => $this->faker->url(),
            'status' => 'pending',
        ];
    }

    public function active(): self
    {
        return $this->state(['status' => 'active', 'approved_at' => now()]);
    }
}
