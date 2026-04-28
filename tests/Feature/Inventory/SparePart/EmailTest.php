<?php

namespace Tests\Feature\Inventory\SparePart;

use App\Livewire\Pages\Inventory\SpareParts\EmailForm;
use App\Mail\SparePartEmail;
use App\Models\TesterSparePart;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class EmailTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        Role::create(['name' => 'Admin']);

        $this->adminUser = User::factory()->create();
        $this->sparePart = TesterSparePart::factory()->create();
        $this->recipient = User::factory()->create();

        $this->adminUser->assignRole('Admin');
    }

    public function test_email_form_prefills_spare_part_data(): void
    {
        $sparePart = TesterSparePart::factory()->create([
            'name' => 'Fuse',
        ]);

        $sparePart->responsibleUsers()->attach($this->recipient);

        $this->actingAs($this->adminUser);

        Livewire::test(EmailForm::class, [
            'sparePartId' => $sparePart->id,
        ])
            ->assertSet('sparePart.id', $sparePart->id)
            ->assertSet('form.subject', 'Spare Part Needs Reordering: Fuse')
            ->assertSet('form.responsible_user_ids', [$this->recipient->id]);
    }

    public function test_recipients_can_be_updated_via_event(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $this->actingAs($this->adminUser);

        Livewire::test(EmailForm::class, [
            'sparePartId' => $this->sparePart->id,
        ])
            ->dispatch('recipientsUpdated', [$user1->id, $user2->id])
            ->assertSet('form.responsible_user_ids', [$user1->id, $user2->id]);
    }

    public function test_email_form_validates_required_fields(): void
    {
        $this->actingAs($this->adminUser);

        Livewire::test(EmailForm::class, [
            'sparePartId' => $this->sparePart->id,
        ])
            ->set('form.responsible_user_ids', [])
            ->set('form.subject', '')
            ->set('form.body', '')
            ->call('save')
            ->assertHasErrors([
                'form.responsible_user_ids',
                'form.subject',
                'form.body',
            ]);
    }

    public function test_email_is_sent_successfully(): void
    {
        Mail::fake();

        $sparePart = $this->sparePart;

        $this->actingAs($this->adminUser);

        Livewire::test(EmailForm::class, [
            'sparePartId' => $sparePart->id,
        ])
            ->set('form.responsible_user_ids', [$this->recipient->id])
            ->set('form.subject', 'Need More Relay Stock')
            ->set('form.body', 'Please reorder this spare part.')
            ->call('save')
            ->assertDispatched('switchTab', tab: 'spare-parts') 
            ->assertDispatched('switchTab', tab: 'spare-part-details', sparePartId: $sparePart->id);

        Mail::assertSent(SparePartEmail::class, function ($mail) use ($sparePart) {
            return $mail->sparePart->id === $sparePart->id
                && $mail->subjectLine === 'Need More Relay Stock'
                && $mail->messageBody === 'Please reorder this spare part.';
        });
    }
}