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

        // TODO: get db data
        $mockItems = [
            [
                'type' => 'issue',
                'tester' => 'Tester 01',
                'date' => now()->addDays(2),
            ],
            [
                'type' => 'maintenance',
                'tester' => 'Tester 02',
                'date' => now()->addDays(7),
            ],
            [
                'type' => 'calibration',
                'tester' => 'Tester 03',
                'date' => now()->addDays(2),
            ],
            [
                'type' => 'calibration',
                'tester' => 'Tester 03',
                'date' => now()->addDays(10),
            ],
            [
                'type' => 'calibration',
                'tester' => 'Tester 03',
                'date' => now()->addDays(10),
            ],
            [
                'type' => 'maintenance',
                'tester' => 'Tester 04',
                'date' => now()->addDays(3),
            ],
            [
                'type' => 'maintenance',
                'tester' => 'Tester 04',
                'date' => now()->addDays(3),
            ],
            [
                'type' => 'maintenance',
                'tester' => 'Tester 04',
                'date' => now()->addDays(3),
            ],
            [
                'type' => 'maintenance',
                'tester' => 'Tester 04',
                'date' => now()->addDays(3),
            ],
            [
                'type' => 'issue',
                'tester' => 'Tester 01',
                'date' => now()->addDays(3),
            ],
        ];

        if ($this->type === 'events') {
            // show everything except for issues
            $filtered = array_filter($mockItems, fn ($item) => $item['type'] !== 'issue');
        } 
        elseif ($this->type === 'issues') {
            // show only issues
            $filtered = array_filter($mockItems, fn ($item) => $item['type'] === 'issue');
        }
        else {
            // show all
            $filtered = $mockItems;
        }

        // reindex array after filtering
        $this->items = array_values($filtered);

        // sort by date
        usort($this->items, function ($a, $b) {
            return $a['date']->timestamp <=> $b['date']->timestamp;
        });

        $this->items = array_slice($this->items, 0, $this->limit);
    }

    public function typeClasses($type) {
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