<?php

namespace App\Livewire\Pages\Services;

use Livewire\Component;
use App\Models\Tester;
use App\Models\TesterMaintenanceSchedule;
use App\Models\TesterCalibrationSchedule;
use App\Models\DataChangeLog;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class MaintenanceSettings extends Component
{
    public bool $isEditing = false;
    
    // Search Data
    public $searchQuery = '';
    public $searchResults = [];

    // Tester Data
    public $selectedTesterId = null;
    public $testerId = '';
    public $testerName = '';

    // Maintenance Data
    public $lastMaintenanceDate = '-';
    public $lastMaintenanceUser = '-';
    public $maintenancePeriodId = '';
    public $maintenancePeriodLabel = '-';
    public $nextMaintenanceDate = '';
    public $nextMaintenanceUser = '-';
    public $customMaintenanceLabel = '';

    // Calibration Data
    public $lastCalibrationDate = '-';
    public $lastCalibrationUser = '-';
    public $calibrationPeriodId = '';
    public $calibrationPeriodLabel = '-';
    public $nextCalibrationDate = '';
    public $nextCalibrationUser = '-';
    public $customCalibrationLabel = '';

    public $maintenanceOptions = [];
    public $calibrationOptions = [];
    public $users = [];
    public $nextMaintenanceUserId = null;
    public $nextCalibrationUserId = null;
    public $preselectedTesterId = null;

    public bool $showAddPeriodModal = false;
    public string $newPeriodType = 'maintenance';
    public int $newMonths = 0;
    public int $newWeeks = 0;
    public int $newDays = 0;

    public function mount()
    {
        $this->loadOptions();

        if ($this->preselectedTesterId) {
            $this->selectTester($this->preselectedTesterId);
        }
    }

    public function loadOptions()
    {
        $this->users = class_exists(\App\Models\User::class)
            ? \App\Models\User::orderBy('first_name')->orderBy('last_name')->get()->map(function($u) {
                return ['id' => $u->id, 'name' => $u->name];
            })->toArray()
            : [];

        $mProcedures = DB::table('tester_maintenance_procedures')
            ->join('procedure_interval_units', 'tester_maintenance_procedures.interval_unit', '=', 'procedure_interval_units.id')
            ->select('tester_maintenance_procedures.id', 'interval_value', 'procedure_interval_units.name as unit', 'type')
            ->orderBy('procedure_interval_units.id', 'desc')
            ->orderBy('interval_value', 'asc')
            ->get();
        
        $this->maintenanceOptions = [];
        foreach ($mProcedures as $p) {
            if (str_starts_with($p->type, 'Custom: ')) {
                $this->maintenanceOptions[$p->id] = str_replace('Custom: ', '', $p->type);
            } else {
                $typeStr = str_replace(['Standard ', 'Full '], '', $p->type);
                $typeStr = trim($typeStr);
                if(empty($typeStr) || strtolower($typeStr) !== 'custom') {
                    $typeStr = "{$p->interval_value} {$p->unit}";
                }
                $this->maintenanceOptions[$p->id] = $typeStr;
            }
        }

        $cProcedures = DB::table('tester_calibration_procedures')
            ->join('procedure_interval_units', 'tester_calibration_procedures.interval_unit', '=', 'procedure_interval_units.id')
            ->select('tester_calibration_procedures.id', 'interval_value', 'procedure_interval_units.name as unit', 'type')
            ->orderBy('procedure_interval_units.id', 'desc')
            ->orderBy('interval_value', 'asc')
            ->get();
            
        $this->calibrationOptions = [];
        foreach ($cProcedures as $p) {
            if (str_starts_with($p->type, 'Custom: ')) {
                $this->calibrationOptions[$p->id] = str_replace('Custom: ', '', $p->type);
            } else {
                $typeStr = str_replace(['Standard ', 'Full '], '', $p->type);
                $typeStr = trim($typeStr);
                if(empty($typeStr) || strtolower($typeStr) !== 'custom') {
                    $typeStr = "{$p->interval_value} {$p->unit}";
                }
                $this->calibrationOptions[$p->id] = $typeStr;
            }
        }
    }

    public function updatedSearchQuery()
    {
        if (strlen($this->searchQuery) > 0) {
            $this->searchResults = Tester::where('id', 'like', '%' . $this->searchQuery . '%')
                ->orWhere('name', 'like', '%' . $this->searchQuery . '%')
                ->limit(5)
                ->get()
                ->toArray();
        } else {
            $this->searchResults = [];
            $this->selectedTesterId = null;
        }
    }

    protected function getRawDate($type) {
        $tester = Tester::with([
            'maintenanceSchedules' => function($q) { $q->orderBy('id', 'desc')->limit(1); }, 
            'calibrationSchedules' => function($q) { $q->orderBy('id', 'desc')->limit(1); }
        ])->find($this->selectedTesterId);
        
        if(!$tester) return Carbon::now();

        if ($type == 'maintenance') {
            $m = $tester->maintenanceSchedules->first();
            return $m && $m->last_maintenance_date ? clone $m->last_maintenance_date : Carbon::now();
        } else {
            $c = $tester->calibrationSchedules->first();
            return $c && $c->last_calibration_date ? clone $c->last_calibration_date : Carbon::now();
        }
    }

    public function selectTester($id)
    {
        $tester = Tester::with([
            'maintenanceSchedules' => function ($q) {
                $q->orderBy('id', 'desc')->limit(1);
            },
            'calibrationSchedules' => function ($q) {
                $q->orderBy('id', 'desc')->limit(1);
            }
        ])->find($id);

        if (!$tester) return;

        $this->selectedTesterId = $tester->id;
        $this->testerId = $tester->id;
        $this->testerName = $tester->name;
        $this->searchQuery = $tester->id . ' - ' . $tester->name;
        $this->searchResults = [];

        // Load Maintenance
        $mSchedule = $tester->maintenanceSchedules->first();
        if ($mSchedule) {
            $this->lastMaintenanceDate = $mSchedule->last_maintenance_date ? $mSchedule->last_maintenance_date->format('j.n.Y H:i') : '-';
            $this->lastMaintenanceUser = class_exists(\App\Models\User::class) && $mSchedule->last_maintenance_by_user_id ? (\App\Models\User::find($mSchedule->last_maintenance_by_user_id)?->name ?? '-') : '-';
            $this->nextMaintenanceDate = $mSchedule->next_maintenance_due ? $mSchedule->next_maintenance_due->format('Y-m-d\TH:i') : '';
            $this->nextMaintenanceUserId = $mSchedule->next_maintenance_by_user_id ?: (auth()->check() ? auth()->id() : null);
            $this->nextMaintenanceUser = class_exists(\App\Models\User::class) && $this->nextMaintenanceUserId ? (\App\Models\User::find($this->nextMaintenanceUserId)?->name ?? '-') : '-';
            $this->maintenancePeriodId = $mSchedule->maintenance_id;
            $this->maintenancePeriodLabel = $this->maintenanceOptions[$mSchedule->maintenance_id] ?? '-';
        } else {
            $this->lastMaintenanceDate = '-';
            $this->lastMaintenanceUser = '-';
            $this->nextMaintenanceDate = '';
            $this->nextMaintenanceUserId = auth()->check() ? auth()->id() : null;
            $this->nextMaintenanceUser = class_exists(\App\Models\User::class) && $this->nextMaintenanceUserId ? (\App\Models\User::find($this->nextMaintenanceUserId)?->name ?? '-') : '-';
            $this->maintenancePeriodId = '';
            $this->maintenancePeriodLabel = '-';
        }

        // Load Calibration
        $cSchedule = $tester->calibrationSchedules->first();
        if ($cSchedule) {
            $this->lastCalibrationDate = $cSchedule->last_calibration_date ? $cSchedule->last_calibration_date->format('j.n.Y H:i') : '-';
            $this->lastCalibrationUser = class_exists(\App\Models\User::class) && $cSchedule->last_calibration_by_user_id ? (\App\Models\User::find($cSchedule->last_calibration_by_user_id)?->name ?? '-') : '-';
            $this->nextCalibrationDate = $cSchedule->next_calibration_due ? $cSchedule->next_calibration_due->format('Y-m-d\TH:i') : '';
            $this->nextCalibrationUserId = $cSchedule->next_calibration_by_user_id ?: (auth()->check() ? auth()->id() : null);
            $this->nextCalibrationUser = class_exists(\App\Models\User::class) && $this->nextCalibrationUserId ? (\App\Models\User::find($this->nextCalibrationUserId)?->name ?? '-') : '-';
            $this->calibrationPeriodId = $cSchedule->calibration_id;
            $this->calibrationPeriodLabel = $this->calibrationOptions[$cSchedule->calibration_id] ?? '-';
        } else {
            $this->lastCalibrationDate = '-';
            $this->lastCalibrationUser = '-';
            $this->nextCalibrationDate = '';
            $this->nextCalibrationUserId = auth()->check() ? auth()->id() : null;
            $this->nextCalibrationUser = class_exists(\App\Models\User::class) && $this->nextCalibrationUserId ? (\App\Models\User::find($this->nextCalibrationUserId)?->name ?? '-') : '-';
            $this->calibrationPeriodId = '';
            $this->calibrationPeriodLabel = '-';
        }
        
        $this->isEditing = false;
    }

    public function updatedMaintenancePeriodId($value)
    {
        if ($value === 'add_new_period') {
            $this->showAddPeriodModal = true;
            $this->newPeriodType = 'maintenance';
            $this->maintenancePeriodId = '';
            return;
        }

        if (!$value || $value === 'custom') return;
        
        $proc = DB::table('tester_maintenance_procedures')
            ->join('procedure_interval_units', 'tester_maintenance_procedures.interval_unit', '=', 'procedure_interval_units.id')
            ->select('interval_value', 'procedure_interval_units.name as unit', 'type')
            ->where('tester_maintenance_procedures.id', $value)
            ->first();

        if ($proc) {
            $date = $this->getRawDate('maintenance');
            
            if (str_starts_with($proc->type, 'Custom: ')) {
                preg_match('/(\d+)\s+Month/i', $proc->type, $m);
                preg_match('/(\d+)\s+Week/i', $proc->type, $w);
                preg_match('/(\d+)\s+Day/i', $proc->type, $d);

                if (!empty($m[1])) $date->addMonths((int)$m[1]);
                if (!empty($w[1])) $date->addWeeks((int)$w[1]);
                if (!empty($d[1])) $date->addDays((int)$d[1]);
            } else {
                $val = (int)$proc->interval_value;
                $unit = strtolower($proc->unit);

                if (str_contains($unit, 'month')) $date->addMonths($val);
                elseif (str_contains($unit, 'year')) $date->addYears($val);
                elseif (str_contains($unit, 'day')) $date->addDays($val);
                elseif (str_contains($unit, 'week')) $date->addWeeks($val);
            }
            
            $this->nextMaintenanceDate = $date->format('Y-m-d\TH:i');
        }
    }

    public function updatedNextMaintenanceDate($value)
    {
        // Removed custom period calculation
    }

    public function updatedCalibrationPeriodId($value)
    {
        if ($value === 'add_new_period') {
            $this->showAddPeriodModal = true;
            $this->newPeriodType = 'calibration';
            $this->calibrationPeriodId = '';
            return;
        }

        if (!$value || $value === 'custom') return;
        
        $proc = DB::table('tester_calibration_procedures')
            ->join('procedure_interval_units', 'tester_calibration_procedures.interval_unit', '=', 'procedure_interval_units.id')
            ->select('interval_value', 'procedure_interval_units.name as unit', 'type')
            ->where('tester_calibration_procedures.id', $value)
            ->first();

        if ($proc) {
            $date = $this->getRawDate('calibration');
            
            if (str_starts_with($proc->type, 'Custom: ')) {
                preg_match('/(\d+)\s+Month/i', $proc->type, $m);
                preg_match('/(\d+)\s+Week/i', $proc->type, $w);
                preg_match('/(\d+)\s+Day/i', $proc->type, $d);

                if (!empty($m[1])) $date->addMonths((int)$m[1]);
                if (!empty($w[1])) $date->addWeeks((int)$w[1]);
                if (!empty($d[1])) $date->addDays((int)$d[1]);
            } else {
                $val = (int)$proc->interval_value;
                $unit = strtolower($proc->unit);

                if (str_contains($unit, 'month')) $date->addMonths($val);
                elseif (str_contains($unit, 'year')) $date->addYears($val);
                elseif (str_contains($unit, 'day')) $date->addDays($val);
                elseif (str_contains($unit, 'week')) $date->addWeeks($val);
            }
            
            $this->nextCalibrationDate = $date->format('Y-m-d\TH:i');
        }
    }

    public function updatedNextCalibrationDate($value)
    {
        // Removed custom period calculation
    }

    public function toggleEdit()
    {
        if (!$this->selectedTesterId) return;

        if ($this->isEditing) {
            $this->save();
            $this->isEditing = false;
        } else {
            $this->isEditing = true;
        }
    }

    public function saveNewPeriod()
    {
        $this->validate([
            'newMonths' => 'integer|min:0|max:120',
            'newWeeks' => 'integer|min:0|max:52',
            'newDays' => 'integer|min:0|max:365',
        ]);

        if ($this->newMonths == 0 && $this->newWeeks == 0 && $this->newDays == 0) {
            return;
        }

        $parts = [];
        if ($this->newMonths > 0) $parts[] = $this->newMonths . ($this->newMonths == 1 ? ' Month' : ' Months');
        if ($this->newWeeks > 0) $parts[] = $this->newWeeks . ($this->newWeeks == 1 ? ' Week' : ' Weeks');
        if ($this->newDays > 0) $parts[] = $this->newDays . ($this->newDays == 1 ? ' Day' : ' Days');
        $label = implode(' ', $parts);

        // Approximate total days just for basic sorting/storage if needed
        $totalDays = ($this->newMonths * 30) + ($this->newWeeks * 7) + $this->newDays; 

        $table = $this->newPeriodType === 'maintenance' ? 'tester_maintenance_procedures' : 'tester_calibration_procedures';
        $unitId = DB::table('procedure_interval_units')->where('name', 'days')->value('id') ?? 1;

        $newId = DB::table($table)->insertGetId([
            'type' => 'Custom: ' . $label,
            'interval_value' => $totalDays,
            'interval_unit' => $unitId,
        ]);

        $this->loadOptions();

        if ($this->newPeriodType === 'maintenance') {
            $this->maintenancePeriodId = $newId;
            $this->updatedMaintenancePeriodId($newId);
        } else {
            $this->calibrationPeriodId = $newId;
            $this->updatedCalibrationPeriodId($newId);
        }

        $this->closeAddPeriodModal();
    }

    public function closeAddPeriodModal()
    {
        $this->showAddPeriodModal = false;
        $this->newMonths = 0;
        $this->newWeeks = 0;
        $this->newDays = 0;
    }

    protected function handleCustomPeriod($type, $dateString)
    {
        $start = $this->getRawDate($type);
        $target = Carbon::parse($dateString);
        $days = $start->diffInDays($target);
        
        if ($days <= 0) $days = 1;

        $unitId = DB::table('procedure_interval_units')->where('name', 'Days')->value('id') ?? 1;

        $table = $type === 'maintenance' ? 'tester_maintenance_procedures' : 'tester_calibration_procedures';
        
        $existing = DB::table($table)
            ->where('interval_value', $days)
            ->where('interval_unit', $unitId)
            ->where('type', 'Custom')
            ->first();
            
        if ($existing) return $existing->id;
        
        return DB::table($table)->insertGetId([
            'type' => 'Custom',
            'interval_value' => $days,
            'interval_unit' => $unitId,
        ]);
    }

    public function save()
    {
        $changes = [];

        // Track Maintenance Changes
        if ($this->maintenancePeriodId && $this->nextMaintenanceDate) {
            $mId = $this->maintenancePeriodId;
            if ($mId === 'custom') {
                $mId = $this->handleCustomPeriod('maintenance', $this->nextMaintenanceDate);
            }

            $mSchedule = TesterMaintenanceSchedule::where('tester_id', $this->selectedTesterId)->latest('id')->first();
            if ($mSchedule) {
                // Determine what changed
                $oldMId = $mSchedule->maintenance_id;
                $oldMDue = $mSchedule->next_maintenance_due ? Carbon::parse($mSchedule->next_maintenance_due)->format('Y-m-d\TH:i') : 'empty';
                $oldMUser = $mSchedule->next_maintenance_by_user_id;

                $newMDue = Carbon::parse($this->nextMaintenanceDate)->format('Y-m-d\TH:i');
                $newMUser = $this->nextMaintenanceUserId ?: (auth()->check() ? auth()->id() : null);

                $mSchedule->update([
                    'maintenance_id' => $mId,
                    'next_maintenance_due' => $this->nextMaintenanceDate,
                    'next_maintenance_by_user_id' => $newMUser,
                ]);

                $mDiff = [];
                if ((string)$oldMId !== (string)$mId) {
                    $oldMLabel = $this->maintenanceOptions[$oldMId] ?? 'Custom';
                    $newMLabel = $this->maintenanceOptions[$mId] ?? 'Custom';
                    $mDiff[] = "- maintenance_period: [{$oldMLabel}] -> [{$newMLabel}]";
                }
                if ($oldMDue !== $newMDue) {
                    $mDiff[] = "- next_maintenance_due: [{$oldMDue}] -> [{$newMDue}]";
                }
                if ((string)$oldMUser !== (string)$newMUser) {
                    $oldU = class_exists(\App\Models\User::class) && $oldMUser ? (\App\Models\User::find($oldMUser)?->name ?? $oldMUser) : 'empty';
                    $newU = class_exists(\App\Models\User::class) && $newMUser ? (\App\Models\User::find($newMUser)?->name ?? $newMUser) : 'empty';
                    $mDiff[] = "- maintenance_user: [{$oldU}] -> [{$newU}]";
                }

                if (!empty($mDiff)) {
                    $changes[] = implode("\n", $mDiff);
                }
            } else {
                $newMUser = $this->nextMaintenanceUserId ?: (auth()->check() ? auth()->id() : null);
                TesterMaintenanceSchedule::create([
                    'tester_id' => $this->selectedTesterId,
                    'maintenance_id' => $mId,
                    'next_maintenance_due' => $this->nextMaintenanceDate,
                    'next_maintenance_by_user_id' => $newMUser,
                    'schedule_created_date' => now(),
                    'maintenance_status' => DB::table('schedule_statuses')->where('name', 'Pending')->value('id') ?? 1,
                ]);

                $mLabel = $this->maintenanceOptions[$mId] ?? 'Custom';
                $mU = class_exists(\App\Models\User::class) && $newMUser ? (\App\Models\User::find($newMUser)?->name ?? $newMUser) : 'empty';
                $changes[] = "- Initialized maintenance schedule:\n  - interval: {$mLabel}\n  - next_due: {$this->nextMaintenanceDate}\n  - user: {$mU}";
            }
        }

        // Track Calibration Changes
        if ($this->calibrationPeriodId && $this->nextCalibrationDate) {
            $cId = $this->calibrationPeriodId;
            if ($cId === 'custom') {
                $cId = $this->handleCustomPeriod('calibration', $this->nextCalibrationDate);
            }

            $cSchedule = TesterCalibrationSchedule::where('tester_id', $this->selectedTesterId)->latest('id')->first();
            if ($cSchedule) {
                // Determine what changed
                $oldCId = $cSchedule->calibration_id;
                $oldCDue = $cSchedule->next_calibration_due ? Carbon::parse($cSchedule->next_calibration_due)->format('Y-m-d\TH:i') : 'empty';
                $oldCUser = $cSchedule->next_calibration_by_user_id;

                $newCDue = Carbon::parse($this->nextCalibrationDate)->format('Y-m-d\TH:i');
                $newCUser = $this->nextCalibrationUserId ?: (auth()->check() ? auth()->id() : null);

                $cSchedule->update([
                    'calibration_id' => $cId,
                    'next_calibration_due' => $this->nextCalibrationDate,
                    'next_calibration_by_user_id' => $newCUser,
                ]);

                $cDiff = [];
                if ((string)$oldCId !== (string)$cId) {
                    $oldCLabel = $this->calibrationOptions[$oldCId] ?? 'Custom';
                    $newCLabel = $this->calibrationOptions[$cId] ?? 'Custom';
                    $cDiff[] = "- calibration_period: [{$oldCLabel}] -> [{$newCLabel}]";
                }
                if ($oldCDue !== $newCDue) {
                    $cDiff[] = "- next_calibration_due: [{$oldCDue}] -> [{$newCDue}]";
                }
                if ((string)$oldCUser !== (string)$newCUser) {
                    $oldU = class_exists(\App\Models\User::class) && $oldCUser ? (\App\Models\User::find($oldCUser)?->name ?? $oldCUser) : 'empty';
                    $newU = class_exists(\App\Models\User::class) && $newCUser ? (\App\Models\User::find($newCUser)?->name ?? $newCUser) : 'empty';
                    $cDiff[] = "- calibration_user: [{$oldU}] -> [{$newU}]";
                }

                if (!empty($cDiff)) {
                    $changes[] = implode("\n", $cDiff);
                }
            } else {
                $newCUser = $this->nextCalibrationUserId ?: (auth()->check() ? auth()->id() : null);
                TesterCalibrationSchedule::create([
                    'tester_id' => $this->selectedTesterId,
                    'calibration_id' => $cId,
                    'next_calibration_due' => $this->nextCalibrationDate,
                    'next_calibration_by_user_id' => $newCUser,
                    'schedule_created_date' => now(),
                    'calibration_status' => DB::table('schedule_statuses')->where('name', 'Pending')->value('id') ?? 1,
                ]);

                $cLabel = $this->calibrationOptions[$cId] ?? 'Custom';
                $cU = class_exists(\App\Models\User::class) && $newCUser ? (\App\Models\User::find($newCUser)?->name ?? $newCUser) : 'empty';
                $changes[] = "- Initialized calibration schedule:\n  - interval: {$cLabel}\n  - next_due: {$this->nextCalibrationDate}\n  - user: {$cU}";
            }
        }

        if (!empty($changes)) {
            DataChangeLog::create([
                'changed_at' => now(),
                'explanation' => "Edited service details:\n" . implode("\n", $changes),
                'tester_id' => $this->selectedTesterId,
                'user_id' => auth()->id() ?? 1,
            ]);
        }

        $this->loadOptions();
        $this->selectTester($this->selectedTesterId);
    }

    public function render()
    {
        return view('livewire.pages.services.maintenance-settings');
    }
}
