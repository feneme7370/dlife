<?php

use App\Models\Page\Platform;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

new class extends Component
{
    use WithFileUploads;
    use WithPagination;
    use \App\Traits\SortTitle;

    //////////////////////////////////////////////////////////////////// PROPIEDADES
    public function mount(){
        $this->sortField = 'name';
        $this->sortDirection = 'asc';
        if($this->type_selected == 'todo'){$this->type_selected = '';}
    }

    //////////////////////////////////////////////////////////////////// PROPIEDADES
    // propiedades de item y titulos
    public $file;
    public $titlePage = 'Plataformas';
    public $subtitlePage = 'Listado de plataformas';
    public $type_selected = 'todo';

    //////////////////////////////////////////////////////////////////// CONSULTA DE LISTADO Y ELIMINAR ITEM
    // consulta de item
    public function querySearch(){
        return Platform::where('user_id', Auth::id())
            ->where(function ($query) {
                $query->where('name', 'like', "%{$this->search}%");
                    //   ->orWhere('slug', 'like', "%{$this->search}%");
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);
    }

    // eliminar item
    public function deleteItem($uuid){
        $item = Platform::where('user_id', Auth::id())->where('uuid', $uuid)->first();
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
        :create-route="'platforms.create'"
        :breadcrumbs="[
            ['label' => 'Dashboard', 'route' => 'dashboard'],
            ['label' => 'Asociaciones', 'route' => 'associations.index'],
            ['label' => $this->titlePage]
        ]"
    />

    {{-- toast de mensaje --}}
    <x-libraries.flux.toast-success />

    {{-- barra de busqueda --}}
    <x-page.partials.input-search />

    {{-- listado de sujetos --}}
    <div class="space-y-2">
        @foreach ($this->querySearch() as $item)
            <div class="flex items-center justify-between">

                <div class="flex items-center gap-3">
                    <p>
                        <a class="hover:underline" href="{{ route('platforms.show', ['platformUuid' => $item->uuid]) }}">{{ $item->name }}</p>
                            <span class="text-xs text-gray-500 dark:text-gray-400 italic">
                                |{{ $item->name }}
                            </span>
                        </a>
                </div>

                <div class="flex items-center justify-center">
                        <a href="{{ route('platforms.edit', ['platformUuid' => $item->uuid]) }}"><flux:button size="xs" variant="ghost" icon="pencil-square"></flux:button></a>
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
    <flux:separator class="mb-2 mt-10" variant="subtle" />
    
    <div class="flex justify-between items-center gap-1">
        <flux:button icon="cloud-arrow-down" class="text-xs text-center" wire:click="export('platforms')">Exp.</flux:button>
        <div class="flex gap-3">
            <flux:button icon="cloud-arrow-up" class="text-xs text-center" wire:click="import('platforms')">Imp.</flux:button>
            <flux:input type="file" wire:model="file" />
        </div>
    </div>

</div>