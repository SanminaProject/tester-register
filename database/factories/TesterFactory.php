<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Tester;
use Illuminate\Support\Facades\DB;

class TesterFactory extends Factory
{
    protected $model = Tester::class;

    public function definition(): array
    {
        return [
            'name' => fake()->unique()->company(),
            'description' => fake()->sentence(),
            'id_number_by_customer' => fake()->optional()->bothify('SN #######'),
            'operating_system' => fake()->randomElement(['Windows 10', 'Linux', 'XP', null]),
            'type' => fake()->randomElement(['FUNC', 'HIPOT', 'SWDOWNLOAD']),
            'product_family' => fake()->randomElement(['ALL', 'DNWP-DYNA', '9S']),
            'manufacturer' => fake()->company(),
            'implementation_date' => fake()->date(),

            // foreign keys
            // TODO: use factories for these tables later too instaed of directly querying the database
            'status' => DB::table('asset_statuses')->inRandomOrder()->value('id'),
            'location_id' => DB::table('tester_and_fixture_locations')->inRandomOrder()->value('id'),
            'owner_id' => DB::table('tester_customers')->inRandomOrder()->value('id'),
        ];
    }
}
