<?php

namespace App\Livewire\Forms;

use Livewire\Form;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use App\Mail\SparePartEmail;

class EmailMessageForm extends Form
{
    public array $responsible_user_ids = [];
    public string $subject = '';
    public string $body = '';

    protected function rules()
    {
        return [
            'responsible_user_ids' => 'required|array|min:1',
            'subject' => 'required|string|max:255',
            'body' => 'required|string',
        ];
    }

    public function send($sparePart)
    {
        $this->validate();

        $recipients = User::whereIn('id', $this->responsible_user_ids)
            ->pluck('email')
            ->toArray();

        Mail::to($recipients)->send(
            new SparePartEmail(
                sparePart: $sparePart,
                messageBody: $this->body,
                subjectLine: $this->subject
            )
        );

        $this->reset();
    }
}