<?php

namespace App\Livewire\Pages\Testers;

use App\Models\Tester;
use App\Models\DataChangeLog;
use Livewire\Component;
use Livewire\Attributes\Computed;
use Illuminate\Support\Facades\Storage;

class TesterDetails extends Component
{
    public Tester $tester;

    public function mount($testerId)
    {
        $this->tester = Tester::with(['owner', 'statusRelation', 'location', 'assets'])->findOrFail($testerId);
    }

    public function deleteTester()
    {
        if (!auth()->user() || !auth()->user()->hasRole('Admin')) {
            abort(403, 'Unauthorized action.');
        }

        $testerId = $this->tester->id;
        $bindings = [];

        // Check if there are fixtures bound to this tester
        $fixturesCount = \App\Models\Fixture::where('tester_id', $testerId)->count();
        if ($fixturesCount > 0) {
            $bindings[] = "$fixturesCount fixture(s)";
        }

        // Check if there are spare parts bound to this tester
        $sparePartsCount = \Illuminate\Support\Facades\DB::table('tester_spare_parts')->where('tester_id', $testerId)->count();
        if ($sparePartsCount > 0) {
            $bindings[] = "$sparePartsCount spare part(s)";
        }

        // Check if there are ongoing maintenance schedules
        $maintenanceSchedulesCount = \App\Models\TesterMaintenanceSchedule::where('tester_id', $testerId)->count();
        if ($maintenanceSchedulesCount > 0) {
            $bindings[] = "$maintenanceSchedulesCount maintenance schedule(s)";
        }

        // Check if there are ongoing calibration schedules
        $calibrationSchedulesCount = \App\Models\TesterCalibrationSchedule::where('tester_id', $testerId)->count();
        if ($calibrationSchedulesCount > 0) {
            $bindings[] = "$calibrationSchedulesCount calibration schedule(s)";
        }
        
        // If there are related parts/entities, block the deletion.
        if (!empty($bindings)) {
            // We use 'message' or 'error' depending on how the frontend handles it. 
            // In most Laravel/Livewire setups, developers look for session('error') for warning alerts.
            session()->flash('error', "Cannot delete tester. It is still associated with: " . implode(', ', $bindings) . ". Please unlink these items before deleting.");
            return;
        }

        $details = [
            "- id: [{$this->tester->id}]",
            "- name: [" . ($this->tester->name ?? 'empty') . "]",
            "- description: [" . ($this->tester->description ?? 'empty') . "]",
            "- id_number_by_customer: [" . ($this->tester->id_number_by_customer ?? 'empty') . "]",
            "- operating_system: [" . ($this->tester->operating_system ?? 'empty') . "]",
            "- type: [" . ($this->tester->type ?? 'empty') . "]",
            "- product_family: [" . ($this->tester->product_family ?? 'empty') . "]",
            "- manufacturer: [" . ($this->tester->manufacturer ?? 'empty') . "]",
            "- implementation_date: [" . ($this->tester->implementation_date ?? 'empty') . "]",
            "- additional_info: [" . ($this->tester->additional_info ?? 'empty') . "]",
            "- location_id: [" . ($this->tester->location_id ?? 'empty') . "]",
            "- owner_id: [" . ($this->tester->owner_id ?? 'empty') . "]",
            "- status: [" . ($this->tester->status ?? 'empty') . "]",
        ];

        foreach ($this->tester->assets as $index => $asset) {
            $details[] = "- asset_no " . ($index + 1) . ": [" . ($asset->asset_no ?? 'empty') . "]";
        }

        // Delete tester's documents folder if exists
        $path = 'testers/' . $testerId . '/documents';
        if (Storage::disk('local')->exists($path)) {
            Storage::disk('local')->deleteDirectory($path);
        }

        // Delete related assets only (since they are tightly coupled to the tester)
        $this->tester->assets()->delete();

        $this->tester->delete();

        DataChangeLog::create([
            'changed_at' => now(),
            'explanation' => "Deleted tester details (ID: $testerId):\n" . implode("\n", $details),
            // The tester has been deleted, so we cannot associate the log point with its ID anymore due to FK constraint.
            'tester_id' => null,
            'user_id' => auth()->id() ?? 1,
        ]);

        session()->flash('message', 'Tester deleted successfully.');
        $this->dispatch('switchTab', ['tab' => 'all']);
    }

    #[Computed]
    public function documents()
    {
        $path = 'testers/' . $this->tester->id . '/documents';
        
        if (Storage::disk('local')->exists($path)) {
            return collect(Storage::disk('local')->files($path))->map(function ($path) {
                return [
                    'name' => basename($path),
                    'path' => $path,
                ];
            });
        }

        return collect([]);
    }

    public function downloadDocument($path)
    {
        return Storage::disk('local')->download($path);
    }

    public function downloadAllDocuments()
    {
        $documents = $this->documents();

        if ($documents->isEmpty()) {
            return;
        }

        $zipName = 'Tester_' . $this->tester->id . '_Documents.zip';
        $tempDir = 'temp';
        
        if (!Storage::disk('local')->exists($tempDir)) {
            Storage::disk('local')->makeDirectory($tempDir);
        }
        
        $zipPath = Storage::disk('local')->path($tempDir . '/' . $zipName);
        
        $zip = new \ZipArchive;
        if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE)) {
            foreach ($documents as $doc) {
                $absolutePath = Storage::disk('local')->path($doc['path']);
                // Use basename to just store the file without the directory structure in the zip
                $zip->addFile($absolutePath, $doc['name']);
            }
            $zip->close();
        }

        return response()->download($zipPath)->deleteFileAfterSend(true);
    }

    public function updateInventoryDate()
    {
        $this->tester->update([
            'last_inventoried_date' => now()
        ]);
        $this->tester->refresh();
    }

    public function render()
    {
        return view('livewire.pages.testers.tester-details');
    }
}
