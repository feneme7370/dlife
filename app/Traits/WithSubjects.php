<?php

namespace App\Traits;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

trait WithSubjects
{
    public $name_subject;

    public function storeSubject($selectedProperty)
    {
        $this->name_subject = Str::title(trim($this->name_subject));

        $this->validate([
            'name_subject' => ['required', 'string', 'max:255'],
        ]);

        $subject = \App\Models\Page\Subject::create([
            'name' => $this->name_subject,
            'slug' => Str::slug($this->name_subject . '-' . Str::random(4)),
            'uuid' => Str::random(24),
            'user_id' => Auth::id(),
        ]);

        $this->reset('name_subject');

        if (method_exists($this, 'subjects')) {
            $this->subjects();
        }

        if (property_exists($this, $selectedProperty)) {
            $this->{$selectedProperty}[] = $subject->id;
        }

        $this->modal('add-subject')->close();
    }
}