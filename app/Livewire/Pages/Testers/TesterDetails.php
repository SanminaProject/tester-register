<?php

namespace App\Livewire\Pages\Testers;

use App\Models\Tester;
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
