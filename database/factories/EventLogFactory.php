<?php

namespace Database\Factories;

use App\Models\EventLog;
use App\Models\Tester;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\EventLog>
 */
class EventLogFactory extends Factory
{
    protected $model = EventLog::class;

    public function definition(): array
    {
        return [
            'tester_id' => Tester::factory(),
            'type' => $this->faker->randomElement(['maintenance', 'calibration', 'issue', 'repair', 'other']),
            'description' => $this->faker->sentence(),
            'event_date' => $this->faker->dateTimeBetween('-30 days')->format('Y-m-d H:i:s'),
            'recorded_by' => $this->faker->name(),
        ];
    }
}
