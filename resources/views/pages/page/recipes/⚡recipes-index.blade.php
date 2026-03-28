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

    //////////////////////////////////////////////////////////////////// PROPIEDADES DE PAGINACION
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

    //////////////////////////////////////////////////////////////////// PROPIEDADES
    // propiedades de item y titulos
    public $titlePage = 'Recetas';
    public $subtitlePage = 'Listado de recetas';

    //////////////////////////////////////////////////////////////////// CONSULTA DE LISTADO Y ELIMINAR ITEM
    // consulta de item
    public function querySearch(){
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
};
?>

<div>
     {{-- titulo, descripcion y breadcrumbs --}}
    <x-page.partials.title-page 
        :title="$this->titlePage"
        :create-route="'recipes.create'"
        :breadcrumbs="[
            ['label' => 'Dashboard', 'route' => 'dashboard'],
            ['label' => $this->titlePage]
        ]"
    />

    {{-- toast de mensaje --}}
    <x-libraries.flux.toast-success />

    {{-- barra de busqueda --}}
    <x-page.partials.input-search />

    {{-- listado de sagas --}}
    <div class="space-y-2">
        @foreach ($this->querySearch() as $item)
            <div class="flex items-center justify-between">

                <div class="flex items-center gap-3">
                    <x-libraries.img-tumb-lightbox 
                        :uri="$item->cover_image_url ? $item->cover_image_url : asset('images/placeholderBook.jpg')" 
                        album="Portadas"
                        class_w_h="h-auto w-9"
                        class="w-10"
                    />
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
        {{ $this->querySearch()->links() }}
    </div>

    {{-- exportacion e importacion de excel --}}
    <livewire:pages::page.partials.export-excel-complete 
        table_export="Recipes"
        table_import="Recipes"
        name_file_export="recipes"
        route_redirect_after_import="recipes.index"
    />

</div>