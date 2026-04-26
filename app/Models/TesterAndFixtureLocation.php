<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TesterAndFixtureLocation extends Model
{
    use HasFactory;

    protected $table = 'tester_and_fixture_locations';
    public $timestamps = false;
}
