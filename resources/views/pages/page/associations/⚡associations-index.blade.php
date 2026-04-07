<?php

use Livewire\Component;

new class extends Component
{
    public $titlePage = 'Asociaciones';
    public $subtitlePage = 'Agregue datos para asociar registros';
};
?>

<div>

    {{-- <livewire:pages::page.composables.cbz-compressed /> --}}

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
    
    {{-- varios --}}
    <flux:separator text="Sujetos, Colecciones, Etiquetas" />
    <div class="grid grid-cols-3 gap-1 mb-3 mt-1">
    
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
        
    </div>


    {{-- generos --}}
    <flux:separator text="Generos" />
    <div class="grid grid-cols-3 gap-1 mb-3 mt-1">
        <a href="{{ route('genres.index', ['type' => '']) }}">
            <flux:card size="sm" class="hover:bg-zinc-50 dark:hover:bg-zinc-700">
                <flux:heading class="flex items-center gap-2">Todos <flux:icon name="arrow-up-right" class="ml-auto text-zinc-400" variant="micro" /></flux:heading>
            </flux:card>
        </a>
        <a href="{{ route('genres.index', ['type' => 'visual']) }}">
            <flux:card size="sm" class="hover:bg-zinc-50 dark:hover:bg-zinc-700">
                <flux:heading class="flex items-center gap-2">Peliculas y Series <flux:icon name="arrow-up-right" class="ml-auto text-zinc-400" variant="micro" /></flux:heading>
            </flux:card>
        </a>
        <a href="{{ route('genres.index', ['type' => 'books']) }}">
            <flux:card size="sm" class="hover:bg-zinc-50 dark:hover:bg-zinc-700">
                <flux:heading class="flex items-center gap-2">Libros <flux:icon name="arrow-up-right" class="ml-auto text-zinc-400" variant="micro" /></flux:heading>
            </flux:card>
        </a>
    </div>
    
    {{-- categorias --}}
    <flux:separator text="Categorías"/>
    <div class="grid grid-cols-3 gap-1 mb-3 mt-1">
        <a href="{{ route('categories.index', ['type' => '']) }}">
            <flux:card size="sm" class="hover:bg-zinc-50 dark:hover:bg-zinc-700">
                <flux:heading class="flex items-center gap-2">Todas<flux:icon name="arrow-up-right" class="ml-auto text-zinc-400" variant="micro" /></flux:heading>
            </flux:card>
        </a>
        <a href="{{ route('categories.index', ['type' => 'diaries']) }}">
            <flux:card size="sm" class="hover:bg-zinc-50 dark:hover:bg-zinc-700">
                <flux:heading class="flex items-center gap-2">Diarios <flux:icon name="arrow-up-right" class="ml-auto text-zinc-400" variant="micro" /></flux:heading>
            </flux:card>
        </a>
        <a href="{{ route('categories.index', ['type' => 'recipes']) }}">
            <flux:card size="sm" class="hover:bg-zinc-50 dark:hover:bg-zinc-700">
                <flux:heading class="flex items-center gap-2">Recetas <flux:icon name="arrow-up-right" class="ml-auto text-zinc-400" variant="micro" /></flux:heading>
            </flux:card>
        </a>
    </div>

</div>