<?php

namespace App\Traits;

use Illuminate\Support\Str;

trait HandlesTags
{
    public $newTag = '';

    public function addTag($property)
    {
        $formatted = str_replace(' ', '', Str::title(trim($this->newTag)));

        if ($formatted && !in_array($formatted, $this->$property)) {
            $this->{$property}[] = $formatted;
        }

        $this->newTag = '';
    }

    public function removeTag($property, $index)
    {
        unset($this->{$property}[$index]);
        $this->{$property} = array_values($this->{$property});
    }
}