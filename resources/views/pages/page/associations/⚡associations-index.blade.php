<?php

use Livewire\Component;

new class extends Component
{
    public $titlePage = 'Asociaciones';
    public $subtitlePage = 'Agregue datos para asociar registros';
};
?>

<div>
    {{-- titulo, descripcion y breadcrumbs --}}
    <x-page.partials.title-page 
        :title="$this->titlePage"
        :create-route="'categories.index'"
        :breadcrumbs="[
            ['label' => 'Dashboard', 'route' => 'dashboard'],
            ['label' => $this->titlePage]
        ]"
    />

    {{-- toast de mensaje --}}
    <x-libraries.flux.toast-success />
    
    <div class="mb-3 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
    
        <a href="{{ route('subjects.index') }}">
            <flux:card size="sm" class="hover:bg-zinc-50 dark:hover:bg-zinc-700">
                <flux:heading class="flex items-center gap-2">Sujetos <flux:icon name="arrow-up-right" class="ml-auto text-zinc-400" variant="micro" /></flux:heading>
            </flux:card>
        </a>
        
        <a href="{{ route('collections.index') }}">
            <flux:card size="sm" class="hover:bg-zinc-50 dark:hover:bg-zinc-700">
                <flux:heading class="flex items-center gap-2">Colecciones <flux:icon name="arrow-up-right" class="ml-auto text-zinc-400" variant="micro" /></flux:heading>
            </flux:card>
        </a>
        <a href="{{ route('tags.index') }}">
            <flux:card size="sm" class="hover:bg-zinc-50 dark:hover:bg-zinc-700">
                <flux:heading class="flex items-center gap-2">Etiquetas <flux:icon name="arrow-up-right" class="ml-auto text-zinc-400" variant="micro" /></flux:heading>
            </flux:card>
        </a>

        <a href="{{ route('genres.index') }}">
            <flux:card size="sm" class="hover:bg-zinc-50 dark:hover:bg-zinc-700">
                <flux:heading class="flex items-center gap-2">Generos <flux:icon name="arrow-up-right" class="ml-auto text-zinc-400" variant="micro" /></flux:heading>
            </flux:card>
        </a>
        
        <a href="{{ route('categories.index') }}">
            <flux:card size="sm" class="hover:bg-zinc-50 dark:hover:bg-zinc-700">
                <flux:heading class="flex items-center gap-2">Categorías <flux:icon name="arrow-up-right" class="ml-auto text-zinc-400" variant="micro" /></flux:heading>
            </flux:card>
        </a>

    </div>

</div>