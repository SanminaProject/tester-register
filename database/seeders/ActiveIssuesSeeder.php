<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ActiveIssuesSeeder extends Seeder
{
    /**
     * Seed 15 active issues using existing testers/users from DB.
     */
    public function run(): void
    {
        $problemTypeId = (int) (DB::table('event_types')
            ->whereRaw('LOWER(name) = ?', ['problem'])
            ->value('id') ?? 0);

        if ($problemTypeId === 0) {
            $problemTypeId = (int) (DB::table('event_types')
                ->whereRaw('LOWER(name) = ?', ['issue'])
                ->value('id') ?? 0);
        }

        if ($problemTypeId === 0) {
            $problemTypeId = (int) DB::table('event_types')->insertGetId([
                'name' => 'problem',
            ]);
        }

        $activeStatusId = (int) (DB::table('issue_statuses')
            ->whereRaw('LOWER(name) = ?', ['active'])
            ->value('id') ?? 0);

        if ($activeStatusId === 0) {
            $activeStatusId = (int) DB::table('issue_statuses')->insertGetId([
                'name' => 'Active',
            ]);
        }

        $testerIds = DB::table('testers')->orderBy('id')->pluck('id')->values()->all();

        if ($testerIds === []) {
            return;
        }

        $userIds = DB::table('users')->orderBy('id')->pluck('id')->values()->all();

        if ($userIds === []) {
            $fallbackUserId = (int) DB::table('users')->insertGetId([
                'first_name' => 'Seed',
                'last_name' => 'User',
                'email' => 'seed.user.' . now()->timestamp . '@example.com',
                'phone' => '0000000000',
                'password' => Hash::make('password123'),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $userIds = [$fallbackUserId];
        }

        DB::table('tester_event_logs')
            ->where('description', 'like', '[SEED][ACTIVE] %')
            ->delete();

        $templates = [
            'Communication timeout during startup diagnostics',
            'Intermittent probe contact error on fixture',
            'Failed self-test sequence on channel module',
            'Unexpected voltage drift detected in measurement stage',
            'I/O handshake mismatch with external controller',
            'Sensor reading out of tolerance during warm-up',
            'Test script halted due to device response delay',
            'Repeated retry state observed in loading routine',
            'Functional validation blocked by unstable signal path',
            'Reference baseline mismatch after reboot cycle',
            'Calibration pre-check flagged boundary violation',
            'Power rail fluctuation detected under load profile',
            'Fixture alignment inconsistency in repeated runs',
            'Data capture interrupted by transient interface fault',
            'Automation sequence paused due to safety interlock',
        ];

        $rows = [];
        $now = now();

        for ($i = 0; $i < 15; $i++) {
            $testerId = (int) $testerIds[$i % count($testerIds)];
            $userId = (int) $userIds[$i % count($userIds)];
            $occurredAt = Carbon::parse($now)->subDays($i)->setTime(8 + ($i % 10), 10 + ($i % 40));

            $rows[] = [
                'date' => $occurredAt->format('Y-m-d H:i:s'),
                'description' => $templates[$i],
                'tester_id' => $testerId,
                'event_type' => $problemTypeId,
                'created_by_user_id' => $userId,
                'resolved_date' => null,
                'resolution_description' => null,
                'resolved_by_user_id' => null,
                'issue_status' => $activeStatusId,
                'maintenance_schedule_id' => null,
                'calibration_schedule_id' => null,
                'parent_event_log_id' => null,
            ];
        }

        DB::table('tester_event_logs')->insert($rows);
    }
}
