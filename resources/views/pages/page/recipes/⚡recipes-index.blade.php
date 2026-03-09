<?php

use App\Models\Page\Recipe;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

new class extends Component
{
    use WithFileUploads;
    use WithPagination;

    // propiedades para paginacion y orden, actualizar al buscar
    public $search = '', $sortField = 'title', $sortDirection = 'asc', $perPage = 10000;
    public function updatingSearch(){$this->resetPage();}

    // funcion para ordenar la tabla
    public function sortBy($field){
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortDirection = 'asc';
        }
        $this->sortField = $field;
    }

    // propiedades de item y titulos
    public $file;
    public $recipes;
    public $titlePage = 'Recetas';
    public $subtitlePage = 'Listado de recetas';

    // consulta de item
    public function queryRecipes(){
        return Recipe::where('user_id', Auth::id())
            ->where(function ($query) {
                $query->where('title', 'like', "%{$this->search}%")
                      ->orWhere('slug', 'like', "%{$this->search}%");
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);
    }

    // eliminar item
    public function deleteItem($codigo){
        $item = Recipe::where('user_id', Auth::id())->where('uuid', $codigo)->first();
        $item->delete();
    }

    // exportar tabla cruda a excel
    public function exportComplete()
    {
        return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\RecipesExport,"recipes_info.xlsx");
    }

    // importar tabla cruda de excel
    public function importComplete()
    {
        $this->validate([
            'file' => 'required|mimes:xlsx,csv'
        ]);

        \Maatwebsite\Excel\Facades\Excel::import(new \App\Imports\RecipesImport, $this->file);

        $this->reset('file');

        session()->flash('success', 'Importación exitosa');
    }
};
?>

<div>
    {{-- titulo, descripcion y breadcrumbs --}}
    <div>
        <div container class="mb-1 space-y-1">
            <flux:heading size="xl" level="1">
                <a href="{{ route('recipes.create') }}"><flux:button size="xs" variant="ghost" icon="plus"></flux:button></a>
                {{ $this->titlePage }}
            </flux:heading>
            <flux:text class="text-base">{{ $this->subtitlePage }}</flux:text>
    
            <flux:breadcrumbs>
                <flux:breadcrumbs.item href="{{ route('dashboard') }}">Dashboard</flux:breadcrumbs.item>
                <flux:breadcrumbs.item>{{ $this->titlePage }}</flux:breadcrumbs.item>
            </flux:breadcrumbs>
    
            <flux:separator variant="subtle" />

            <flux:badge color="fuchsia"><a href="{{ route('rcategories.index') }}">Categorias</a></flux:badge>
            <flux:badge color="fuchsia"><a href="{{ route('rtags.index') }}">Etiquetas</a></flux:badge>
        </div>
    </div>

    {{-- toast de mensaje --}}
    <x-libraries.flux.toast-success />

    {{-- buscador --}}
    <div class="mb-3">
        <flux:input type="text" label="Buscar" wire:model.live.debounce.250ms="search" placeholder="Buscar" autofocus/>
    </div>

    {{-- listado de sagas --}}
    <div class="space-y-2">
        @foreach ($this->queryRecipes() as $item)
            <div class="flex items-center justify-between">

                <div class="flex items-center gap-3">
                    <img src="{{ $item->cover_image_url }}" class="w-8 h-8 bg-cover rounded-sm" alt="">
                    <p><a class="hover:underline" href="{{ route('recipes.show', ['recipeUuid' => $item->uuid]) }}">{{ $item->title }}</p></a>
                </div>

                <div class="flex items-center justify-center">
                        <a href="{{ route('recipes.edit', ['recipeUuid' => $item->uuid]) }}"><flux:button size="xs" variant="ghost" icon="pencil-square"></flux:button></a>
                        <a><flux:button size="xs" variant="ghost" icon="trash" wire:confirm="Quiere eliminar?" wire:click="deleteItem('{{ $item->uuid }}')"></flux:button></a>
                </div>

            </div>
        @endforeach
    </div>

    {{-- paginacion --}}
    <div class="mt-3">
        {{ $this->queryRecipes()->links() }}
    </div>

    {{-- exportacion e importacion de excel --}}
    <flux:separator class="mb-2 mt-10" variant="subtle" />
    <div class="flex justify-between items-center gap-1">
        <flux:button icon="cloud-arrow-down" class="text-xs text-center" wire:click="exportComplete()">Exp.</flux:button>
        <div class="flex gap-3">
            <flux:button icon="cloud-arrow-up" class="text-xs text-center" wire:click="importComplete()">Imp.</flux:button>
            <flux:input type="file" wire:model="file" />
        </div>
    </div>

</div>