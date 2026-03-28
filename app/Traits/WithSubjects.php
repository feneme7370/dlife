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

        $subject = \App\Models\Page\Subject::firstOrCreate(
            [
                'name' => $this->name_subject,
                'user_id' => Auth::id(),
            ],
            [
                'slug' => Str::slug($this->name_subject . '-' . Auth::id()),
                'uuid' => Str::random(24),
            ]
        );

        $this->reset('name_subject');

        if (method_exists($this, 'subjects')) {
            $this->subjects();
        }

        if (property_exists($this, $selectedProperty)) {
            $this->{$selectedProperty}[] = $subject->id;
        }

        $this->modal('add-subject')->close();
    }

    public function selectSubject($name)
    {
        $actor = collect($this->subjects())->firstWhere('name', $name);

        if ($actor && !in_array($actor['id'], $this->selectedMovieSubjects)) {
            $this->selectedMovieSubjects[] = $actor['id'];
        }

        if (!$actor) {
            $this->name_subject = $name;
            $this->storeSubject('selectedMovieSubjects');
        }
    }

    public function selectSubjectSerie($name)
    {
        $actor = collect($this->subjects())->firstWhere('name', $name);

        if ($actor && !in_array($actor['id'], $this->selectedSerieSubjects)) {
            $this->selectedSerieSubjects[] = $actor['id'];
        }

        if (!$actor) {
            $this->name_subject = $name;
            $this->storeSubject('selectedSerieSubjects');
        }
    }

    public function selectSubjectBook($name)
    {
        $actor = collect($this->subjects())->firstWhere('name', $name);

        if ($actor && !in_array($actor['id'], $this->selectedBookSubjects)) {
            $this->selectedBookSubjects[] = $actor['id'];
        }

        if (!$actor) {
            $this->name_subject = $name;
            $this->storeSubject('selectedBookSubjects');
        }
    }
}