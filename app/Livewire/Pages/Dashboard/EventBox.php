<?php

namespace App\Livewire\Pages\Dashboard;

use Livewire\Component;

class EventBox extends Component
{
    public $title;
    public $items = [];
    public $type;
    public $limit;

    public function mount($title = "All Events", $type = 'all', $limit = 4)
    {
        $this->type = $type;
        $this->title = $title;
        $this->limit = $limit;

        if ($this->type === 'events') {
            // Fetch real maintenance and calibration events
            $start = now();
            $end = now()->addMonths(6); // Look ahead 6 months

            $mSchedules = \Illuminate\Support\Facades\DB::table('tester_maintenance_schedules as m')
                ->join('testers as t', 'm.tester_id', '=', 't.id')
                ->selectRaw("
                    'maintenance' as type,
                    t.name as tester,
                    m.next_maintenance_due as date
                ")
                ->whereNotNull('m.next_maintenance_due')
                ->whereBetween('m.next_maintenance_due', [$start, $end]);

            $cSchedules = \Illuminate\Support\Facades\DB::table('tester_calibration_schedules as c')
                ->join('testers as t', 'c.tester_id', '=', 't.id')
                ->selectRaw("
                    'calibration' as type,
                    t.name as tester,
                    c.next_calibration_due as date
                ")
                ->whereNotNull('c.next_calibration_due')
                ->whereBetween('c.next_calibration_due', [$start, $end]);

            $all = $mSchedules->unionAll($cSchedules)->get();

            $this->items = $all->map(function ($item) {
                return [
                    'type' => $item->type,
                    'tester' => $item->tester,
                    'date' => \Carbon\Carbon::parse($item->date),
                ];
            })->sortBy('date')->take($this->limit)->values()->toArray();
        } 
        elseif ($this->type === 'issues') {
            // Mock issues for now
            $mockItems = [
                [
                    'type' => 'issue',
                    'tester' => 'Tester 01',
                    'date' => now()->addDays(2),
                ],
                [
                    'type' => 'issue',
                    'tester' => 'Tester 01',
                    'date' => now()->addDays(3),
                ],
            ];
            $this->items = array_slice($mockItems, 0, $this->limit);
        }
        else {
            $this->items = [];
        }
    }

    public function getTypeClasses($type) {
        return match($type) {
            'issue' => 'bg-red-100 text-red-700',
            'maintenance' => 'bg-yellow-100 text-yellow-700',
            'calibration' => 'bg-blue-100 text-blue-700',
            default => 'bg-gray-100 text-gray-700',
        };
    }

    public function render()
    {
        return view('livewire.pages.dashboard.event-box');
    }
}