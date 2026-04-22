<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TesterEventLogsSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $csvFile = database_path('seeders/Tester_log_data.csv');

        if (! file_exists($csvFile)) {
            return;
        }

        $fileHandle = fopen($csvFile, 'r');

        if ($fileHandle === false) {
            return;
        }

        fgetcsv($fileHandle);

        $defaultEventTypeId = DB::table('event_types')
            ->where('name', 'problem')
            ->value('id');

        if ($defaultEventTypeId === null) {
            $defaultEventTypeId = DB::table('event_types')->insertGetId([
                'name' => 'problem',
            ]);
        }

        $activeIssueStatusId = DB::table('issue_statuses')
            ->whereRaw('LOWER(name) = ?', ['active'])
            ->value('id');

        if ($activeIssueStatusId === null) {
            $activeIssueStatusId = DB::table('issue_statuses')->insertGetId([
                'name' => 'Active',
            ]);
        }

        $solvedIssueStatusId = DB::table('issue_statuses')
            ->whereRaw('LOWER(name) = ?', ['solved'])
            ->value('id');

        if ($solvedIssueStatusId === null) {
            $solvedIssueStatusId = DB::table('issue_statuses')->insertGetId([
                'name' => 'Solved',
            ]);
        }

        while (($row = fgetcsv($fileHandle, 0, ',')) !== false) {
            if (count($row) < 7) {
                continue;
            }

            $logId = trim((string) $row[0]);
            $legacyTesterId = (int) $row[1];
            $entryDate = trim((string) $row[2]);
            $indication = trim((string) $row[3]);
            $solution = trim((string) $row[4]);
            $detector = trim((string) $row[5]);
            $solvedDate = trim((string) $row[6]);
            $explicitType = isset($row[7]) ? strtolower(trim((string) $row[7])) : null;

            $userId = $this->resolveOrCreateUserId($detector);
            $testerId = $this->resolveTesterId($legacyTesterId, $indication, $solution);

            if ($testerId === null) {
                continue;
            }

            $formattedEntryDate = Carbon::parse($entryDate)->format('Y-m-d H:i:s');
            $formattedSolvedDate = null;

            if ($solvedDate !== '' && $solvedDate !== '-') {
                $formattedSolvedDate = Carbon::parse($solvedDate)->format('Y-m-d H:i:s');
            }

            $problemTypeId = $this->resolveEventTypeId('problem') ?? (int) $defaultEventTypeId;
            $solutionTypeId = $this->resolveEventTypeId('solution') ?? (int) $defaultEventTypeId;

            $problemIssueStatusId = (int) ($formattedSolvedDate || $solution !== '' ? $solvedIssueStatusId : $activeIssueStatusId);

            $problemPayload = [
                'date' => $formattedEntryDate,
                'description' => $indication,
                'tester_id' => $testerId,
                'event_type' => (int) $problemTypeId,
                'created_by_user_id' => $userId,
                'resolved_date' => $formattedSolvedDate,
                'resolution_description' => $solution !== '' ? $solution : null,
                'resolved_by_user_id' => $formattedSolvedDate ? $userId : null,
                'issue_status' => $problemIssueStatusId,
                'maintenance_schedule_id' => null,
                'calibration_schedule_id' => null,
                'parent_event_log_id' => null,
            ];

            if ($logId !== '') {
                DB::table('tester_event_logs')->updateOrInsert(['id' => (int) $logId], $problemPayload);
            } else {
                $logId = (string) DB::table('tester_event_logs')->insertGetId($problemPayload);
            }

            $shouldCreateSolution = $explicitType === 'solution'
                || $solution !== ''
                || $formattedSolvedDate !== null;

            if (! $shouldCreateSolution) {
                continue;
            }

            DB::table('tester_event_logs')->insert([
                'date' => $formattedSolvedDate ?? $formattedEntryDate,
                'description' => $solution !== '' ? $solution : $indication,
                'tester_id' => $testerId,
                'event_type' => (int) $solutionTypeId,
                'created_by_user_id' => $userId,
                'resolved_date' => $formattedSolvedDate ?? $formattedEntryDate,
                'resolution_description' => $solution !== '' ? $solution : $indication,
                'resolved_by_user_id' => $userId,
                'issue_status' => (int) $solvedIssueStatusId,
                'maintenance_schedule_id' => null,
                'calibration_schedule_id' => null,
                'parent_event_log_id' => (int) $logId,
            ]);
        }

        fclose($fileHandle);
    }

    private function resolveOrCreateUserId(string $detector): int
    {
        $normalizedDetector = trim($detector);

        $existingUser = DB::table('users')
            ->where('first_name', $normalizedDetector)
            ->first();

        if ($existingUser !== null) {
            return (int) $existingUser->id;
        }

        $baseEmail = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $normalizedDetector));
        $email = $baseEmail . rand(100, 999) . '@example.com';

        return (int) DB::table('users')->insertGetId([
            'first_name' => $normalizedDetector,
            'last_name' => 'Unknown',
            'email' => $email,
            'phone' => '000000000',
            'password' => Hash::make('password123'),
        ]);
    }

    private function resolveEventTypeId(string $name): ?int
    {
        $eventTypeId = DB::table('event_types')
            ->whereRaw('LOWER(name) = ?', [strtolower(trim($name))])
            ->value('id');

        return $eventTypeId !== null ? (int) $eventTypeId : null;
    }

    private function resolveTesterId(int $legacyTesterId, string $indication, string $solution): ?int
    {
        $existingTesterId = DB::table('testers')
            ->where('id', $legacyTesterId)
            ->value('id');

        if ($existingTesterId !== null) {
            return (int) $existingTesterId;
        }

        $searchText = strtolower($indication . ' ' . $solution);

        $legacyMappings = [
            160 => 'TAKAYA FLYING PROBE APT 8400CE',
            595 => 'TAKAYA FLYING PROBE APT 8400CE #2',
            628 => 'DIT1',
            630 => 'DST1',
            736 => '9S_SWDL1',
            776 => '9S_SWDL2',
            786 => 'EST2',
        ];

        $mappedName = $legacyMappings[$legacyTesterId] ?? null;

        if ($mappedName === null) {
            if (str_contains($searchText, 'takaya_1')) {
                $mappedName = 'TAKAYA FLYING PROBE APT 8400CE';
            } elseif (str_contains($searchText, 'takaya_2')) {
                $mappedName = 'TAKAYA FLYING PROBE APT 8400CE #2';
            } elseif (str_contains($searchText, 'dst1') || str_contains($searchText, 't32005') || str_contains($searchText, 't32003') || str_contains($searchText, 't32004') || str_contains($searchText, 't33108')) {
                $mappedName = 'DST1';
            } elseif (str_contains($searchText, 'dit1') || str_contains($searchText, 'dyna2')) {
                $mappedName = 'DIT1';
            } elseif (str_contains($searchText, '9s') || str_contains($searchText, 'ccu') || str_contains($searchText, 'csr') || str_contains($searchText, 'tag')) {
                $mappedName = '9S_SWDL1';
            } elseif (str_contains($searchText, 'est2') || str_contains($searchText, 'safetytester')) {
                $mappedName = 'EST2';
            }
        }

        if ($mappedName === null) {
            return null;
        }

        $testerId = DB::table('testers')
            ->where('name', $mappedName)
            ->value('id');

        return $testerId !== null ? (int) $testerId : null;
    }
}

