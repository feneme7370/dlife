<?php

use App\Models\Page\Category;
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
    public $search = '', $sortField = 'name', $sortDirection = 'asc', $perPage = 10000;
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

    //////////////////////////////////////////////////////////////////// FUNCIONES PARA FILTRAR
    // mostrar variables en queryString
    protected function queryString(){
        return [
        'type_selected' => [ 'as' => 'type' ],
        ];
    }

    //////////////////////////////////////////////////////////////////// PROPIEDADES
    // propiedades de item y titulos
    public $file;
    public $titlePage = 'Categorias';
    public $subtitlePage = 'Listado de categorias';
    public $type_selected = 'todo';

    //////////////////////////////////////////////////////////////////// CONSULTA DE LISTADO Y ELIMINAR ITEM
    // consulta de item
    public function queryCategories(){
        return Category::where('user_id', Auth::id())
            ->where(function ($query) {
                $query->where('name', 'like', "%{$this->search}%")
                      ->orWhere('slug', 'like', "%{$this->search}%");
            })
            ->when($this->type_selected, function ($query) {
                $query->where('category_type', $this->type_selected);
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);
    }

    // eliminar item
    public function deleteItem($uuid){
        $item = Category::where('user_id', Auth::id())->where('uuid', $uuid)->first();
        $item->delete();
    }

    //////////////////////////////////////////////////////////////////// EXPORTAR E IMPORTAR EXCEL
    // exportar tabla cruda a excel
    public function export($table){
        $data = \Illuminate\Support\Facades\DB::table($table)->where('user_id', Auth::id())->get();
        return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\GenericExport($data, $table),"{$table}.xlsx");
    }

    // importar tabla cruda de excel
    public function import($table){
        $this->validate(['file' => 'required|mimes:xlsx,csv']);
        \Maatwebsite\Excel\Facades\Excel::import(new \App\Imports\GenericImport($table), $this->file);
        $this->reset('file');
        session()->flash('success', 'Importación exitosa');
    }
};
?>

<div>
    {{-- titulo, descripcion y breadcrumbs --}}
    <x-page.partials.title-page 
        :title="$this->titlePage"
        :create-route="'categories.create'"
        :breadcrumbs="[
            ['label' => 'Dashboard', 'route' => 'dashboard'],
            ['label' => 'Asociaciones', 'route' => 'associations.index'],
            ['label' => $this->titlePage]
        ]"
    />

    {{-- toast de mensaje --}}
    <x-libraries.flux.toast-success />

    {{-- selector de tipo --}}
    <flux:radio.group class="mb-1" wire:model.live="type_selected" label="Seleccionar tipo">
        <div class="flex items-center gap-2 justify-start">
            <flux:radio value="" label="Todos" checked />
            <flux:radio value="diaries" label="Diarios" />
            <flux:radio value="recipes" label="Recetas" />
        </div>
    </flux:radio.group>

    {{-- barra de busqueda --}}
    <x-page.partials.input-search />

    {{-- listado de sujetos --}}
    <div class="space-y-2">
        @foreach ($this->queryCategories() as $item)
            <div class="flex items-center justify-between">

                <div class="flex items-center gap-3">
                    <img src="{{ $item->cover_image_url }}" class="w-8 h-8 bg-cover rounded-sm" alt="">
                    <p>
                        <a class="hover:underline" href="{{ route('categories.show', ['categoryUuid' => $item->uuid]) }}">{{ $item->name }}</p>
                            <span class="text-xs text-gray-500 dark:text-gray-400 italic">
                                |{{ $item->category_type }}
                            </span>
                        </a>
                </div>

                <div class="flex items-center justify-center">
                        <a href="{{ route('categories.edit', ['categoryUuid' => $item->uuid]) }}"><flux:button size="xs" variant="ghost" icon="pencil-square"></flux:button></a>
                        <a><flux:button size="xs" variant="ghost" icon="trash" wire:confirm="Quiere eliminar?" wire:click="deleteItem('{{ $item->uuid }}')"></flux:button></a>
                </div>

            </div>
        @endforeach
    </div>

    {{-- paginacion --}}
    <div class="mt-3">
        {{ $this->queryCategories()->links() }}
    </div>

    {{-- exportacion e importacion de excel --}}
    <flux:separator class="mb-2 mt-10" variant="subtle" />
    
    <div class="flex justify-between items-center gap-1">
        <flux:button icon="cloud-arrow-down" class="text-xs text-center" wire:click="export('categories')">Exp.</flux:button>
        <div class="flex gap-3">
            <flux:button icon="cloud-arrow-up" class="text-xs text-center" wire:click="import('categories')">Imp.</flux:button>
            <flux:input type="file" wire:model="file" />
        </div>
    </div>

</div>