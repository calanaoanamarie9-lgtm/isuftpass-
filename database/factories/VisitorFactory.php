<?php

namespace Database\Factories;

use App\Models\Visitor;
use App\Support\CampusTime;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Visitor>
 */
class VisitorFactory extends Factory
{
    protected $model = Visitor::class;

    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'purpose' => fake()->randomElement(['Meeting', 'Delivery', 'Campus tour', 'Interview']),
            'destination_office' => fake()->randomElement(['Registrar', 'SSC', 'Cashier', 'Library']),
            'contact_number' => fake()->numerify('09#########'),
            'valid_until' => CampusTime::today(),
            'status' => Visitor::STATUS_ACTIVE,
        ];
    }
}
