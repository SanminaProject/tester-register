<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\DataChangeLog;
use App\Models\Fixture;
use App\Models\Tester;
use App\Models\TesterSparePart;
use App\Models\TesterSparePartSupplier;
use App\Models\User;

class DataChangeLogFactory extends Factory
{
    protected $model = DataChangeLog::class;

    public function definition(): array
    {
        return [
            'changed_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'explanation' => $this->faker->sentence(),
            'tester_id' => null,
            'fixture_id' => null,
            'spare_part_id' => null,
            'spare_part_supplier_id' => null,
            'user_id' => User::factory(),
        ];
    }

    public function forFixture(Fixture $fixture)
    {
        return $this->state([
            'fixture_id' => $fixture->id,
            'user_id' => $fixture->tester?->id ? User::factory() : User::factory(),
        ]);
    }
}
