<?php

namespace Tests\Feature;

use App\Livewire\Pages\Issues\IssueWorkbench;
use App\Livewire\Pages\Issues\IssueHistory;
use App\Models\EventType;
use App\Models\IssueStatus;
use App\Models\TesterEventLog;
use App\Models\User;
use App\Models\Tester;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class IssueTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminRole = Role::create(['name' => 'Admin']);

        $this->adminUser = User::factory()->create();
        $this->normalUser = User::factory()->create();
        $this->tester = Tester::factory()->create();

        $this->problemEventType = EventType::create(['name' => 'problem']);
        $this->solutionEventType = EventType::create(['name' => 'solution']);
        $this->activeIssueStatus = IssueStatus::create(['name' => 'Active']);
        $this->solvedIssueStatus = IssueStatus::create(['name' => 'Solved']);

        $this->adminUser->assignRole($this->adminRole);
    }

    public function test_issue_page_loads(): void
    {
        $this->actingAs($this->normalUser);

        $this->get('/issues')
            ->assertOk()
            ->assertSeeLivewire(IssueWorkbench::class);
    }

    public function test_add_new_issue_page_initializes_empty_form(): void
    {
        $this->actingAs($this->normalUser);

        Livewire::test(IssueWorkbench::class, [
            'requestedTab' => 'add',
        ])
            ->assertSet('issueForm.tester_id', null)
            ->assertSet('issueForm.description', '')
            ->assertSet('issueForm.created_by_user_id', $this->normalUser->id)
            ->assertSet('issueForm.status_id', $this->activeIssueStatus->id)
            ->assertSet('mode', 'add_issue')
            ->assertSet('showInlineForm', true);
    }

    public function test_add_new_issue_page_shows_existing_issues_table(): void
    {
        $existingIssue = TesterEventLog::factory()->create([
            'description' => 'Existing Printer Issue',
            'event_type' => $this->problemEventType->id,
            'issue_status' => $this->activeIssueStatus->id,
        ]);

        $this->actingAs($this->normalUser);

        Livewire::test(IssueWorkbench::class, [
            'requestedTab' => 'add',
        ])
            ->assertSee('Existing Printer Issue')
            ->assertSet('mode', 'add_issue')
            ->assertSet('showInlineForm', true);
    }

    public function test_active_issues_page_shows_existing_issues_table(): void
    {
        $existingIssue = TesterEventLog::factory()->create([
            'description' => 'Existing Printer Issue',
            'event_type' => $this->problemEventType->id,
            'issue_status' => $this->activeIssueStatus->id,
        ]);

        $this->actingAs($this->normalUser);

        Livewire::test(IssueWorkbench::class, [
            'requestedTab' => 'all',
        ])
            ->assertSee('Existing Printer Issue')
            ->assertSet('mode', 'active')
            ->assertSet('showInlineForm', false);
    }

    public function test_issue_history_page_shows_issue_history_table(): void
    {
        $problem = TesterEventLog::factory()->create([
            'description' => 'Historical Printer Problem',
            'event_type' => $this->problemEventType->id,
            'issue_status' => $this->activeIssueStatus->id,
            'tester_id' => $this->tester->id,
            'created_by_user_id' => $this->normalUser->id,
        ]);

        $solution = TesterEventLog::factory()->create([
            'description' => 'Replaced toner cartridge',
            'resolution_description' => 'Replaced toner cartridge',
            'event_type' => $this->solutionEventType->id,
            'issue_status' => $this->solvedIssueStatus->id,
            'tester_id' => $this->tester->id,
            'created_by_user_id' => $this->normalUser->id,
            'parent_event_log_id' => $problem->id,
        ]);

        $this->actingAs($this->normalUser);

        Livewire::test(IssueHistory::class)
            ->assertSee('Historical Printer Problem')
            ->assertSee('Replaced toner cartridge')
            ->assertSee('Problem')
            ->assertSee('Solution');
    }

    public function test_user_can_create_issue(): void
    {
        $this->actingAs($this->normalUser);

        Livewire::test(IssueWorkbench::class)
            ->call('beginAddIssue')
            ->set('issueForm.date', now()->format('Y-m-d\TH:i'))
            ->set('issueForm.tester_id', $this->tester->id)
            ->set('issueForm.description', 'Printer broken')
            ->set('issueForm.created_by_user_id', $this->normalUser->id)
            ->set('issueForm.status_id', $this->activeIssueStatus->id)
            ->call('save');

        $this->assertDatabaseHas('tester_event_logs', [
            'description' => 'Printer broken',
            'tester_id' => $this->tester->id,
            'issue_status' => $this->activeIssueStatus->id,
            'event_type' => $this->problemEventType->id,
        ]);
    }

    public function test_user_can_add_solution_to_issue(): void
    {
        $issue = TesterEventLog::factory()->create([
            'tester_id' => $this->tester->id,
            'created_by_user_id' => $this->normalUser->id,
            'event_type' => $this->problemEventType->id,
            'issue_status' => $this->activeIssueStatus->id,
            'description' => 'Printer broken',
        ]);

        $this->actingAs($this->normalUser);

        Livewire::test(IssueWorkbench::class)
            ->call('beginAddSolution', $issue->id)
            ->set('solutionForm.resolution_date', now()->format('Y-m-d\TH:i'))
            ->set('solutionForm.resolution_description', 'Replaced toner')
            ->set('solutionForm.resolved_by_user_id', $this->normalUser->id)
            ->call('save');

        $this->assertDatabaseHas('tester_event_logs', [
            'id' => $issue->id,
            'resolution_description' => 'Replaced toner',
            'issue_status' => $this->solvedIssueStatus->id,
        ]);
    }

    public function test_created_issue_appears_in_active_issue_list(): void
    {
        $issue = TesterEventLog::factory()->create([
            'description' => 'Visible Issue',
            'event_type' => $this->problemEventType->id,
            'issue_status' => $this->activeIssueStatus->id,
        ]);

        $this->actingAs($this->normalUser);

        Livewire::test(IssueWorkbench::class, [
            'requestedTab' => 'all',
            'requestedIssueId' => null,
        ])
            ->assertSee('Visible Issue');
    }

    public function test_issue_requires_required_fields(): void
    {
        $this->actingAs($this->normalUser);

        Livewire::test(IssueWorkbench::class)
            ->call('beginAddIssue')
            ->set('issueForm.date', '')
            ->set('issueForm.tester_id', null)
            ->set('issueForm.description', '')
            ->set('issueForm.created_by_user_id', null)
            ->call('save')
            ->assertHasErrors([
                'issueForm.date' => 'required',
                'issueForm.tester_id' => 'required',
                'issueForm.description' => 'required',
                'issueForm.created_by_user_id' => 'required',
            ]);
    }

    public function test_description_cannot_exceed_max_length(): void
    {
        $this->actingAs($this->normalUser);

        Livewire::test(IssueWorkbench::class)
            ->call('beginAddIssue')
            ->set('issueForm.date', now()->format('Y-m-d\TH:i'))
            ->set('issueForm.tester_id', $this->tester->id)
            ->set('issueForm.description', str_repeat('A', 2001))
            ->set('issueForm.created_by_user_id', $this->normalUser->id)
            ->call('save')
            ->assertHasErrors([
                'issueForm.description' => 'max',
            ]);
    }

    public function test_created_issue_defaults_to_problem_event_type(): void
    {
        $this->actingAs($this->normalUser);

        Livewire::test(IssueWorkbench::class)
            ->call('beginAddIssue')
            ->set('issueForm.date', now()->format('Y-m-d\TH:i'))
            ->set('issueForm.tester_id', $this->tester->id)
            ->set('issueForm.description', 'System crash')
            ->set('issueForm.created_by_user_id', $this->normalUser->id)
            ->set('issueForm.status_id', $this->activeIssueStatus->id)
            ->call('save');

        $issue = TesterEventLog::first();

        $this->assertEquals($this->problemEventType->id, $issue->event_type);
    }

    public function test_issue_form_resets_after_successful_save(): void
    {
        $this->actingAs($this->normalUser);

        Livewire::test(IssueWorkbench::class)
            ->call('beginAddIssue')
            ->set('issueForm.date', now()->format('Y-m-d\TH:i'))
            ->set('issueForm.tester_id', $this->tester->id)
            ->set('issueForm.description', 'Temporary issue')
            ->set('issueForm.created_by_user_id', $this->normalUser->id)
            ->set('issueForm.status_id', $this->activeIssueStatus->id)
            ->call('save')
            ->assertSet('issueForm.tester_id', null)
            ->assertSet('issueForm.description', '');
    }

    public function test_save_dispatches_switch_tab_event_after_creation(): void
    {
        $this->actingAs($this->normalUser);

        Livewire::test(IssueWorkbench::class)
            ->call('beginAddIssue')
            ->set('issueForm.date', now()->format('Y-m-d\TH:i'))
            ->set('issueForm.tester_id', $this->tester->id)
            ->set('issueForm.description', 'Another issue')
            ->set('issueForm.created_by_user_id', $this->normalUser->id)
            ->set('issueForm.status_id', $this->activeIssueStatus->id)
            ->call('save')
            ->assertDispatched('switchTab');
    }
}