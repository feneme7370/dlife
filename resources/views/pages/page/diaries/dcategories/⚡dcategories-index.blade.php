<?php

use App\Models\Page\Dcategory;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

new class extends Component
{
    use WithFileUploads;
    use WithPagination;

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

    // propiedades de item y titulos
    public $file;
    public $dcategories;
    public $title = 'Categorias';
    public $subtitle = 'Listado de categorias';

    // consulta de item
    public function queryDcategories(){
        return Dcategory::where('user_id', Auth::id())
            ->where(function ($query) {
                $query->where('name', 'like', "%{$this->search}%")
                      ->orWhere('slug', 'like', "%{$this->search}%");
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);
    }

    // eliminar item
    public function deleteItem($codigo){
        $item = Dcategory::where('user_id', Auth::id())->where('uuid', $codigo)->first();
        $item->delete();
    }

    // exportar tabla cruda a excel
    public function export($table)
    {
        $data = \Illuminate\Support\Facades\DB::table($table)->where('user_id', Auth::id())->get();

        return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\GenericExport($data, $table),"{$table}.xlsx");
    }

    // importar tabla cruda de excel
    public function import($table)
    {
        $this->validate([
            'file' => 'required|mimes:xlsx,csv'
        ]);

        \Maatwebsite\Excel\Facades\Excel::import(new \App\Imports\GenericImport($table), $this->file);

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
                <a href="{{ route('dcategories.create') }}"><flux:button size="xs" variant="ghost" icon="plus"></flux:button></a>
                {{ $this->title }}
            </flux:heading>
            <flux:text class="text-base">{{ $this->subtitle }}</flux:text>
    
            <flux:breadcrumbs>
                <flux:breadcrumbs.item href="{{ route('dashboard') }}">Dashboard</flux:breadcrumbs.item>
                <flux:breadcrumbs.item href="{{ route('diaries.index') }}">Diario</flux:breadcrumbs.item>
                <flux:breadcrumbs.item>{{ $this->title }}</flux:breadcrumbs.item>
            </flux:breadcrumbs>
    
            <flux:separator variant="subtle" />
        </div>
    </div>

    {{-- buscador --}}
    <div class="mb-3">
        <flux:input type="text" label="Buscar" wire:model.live.debounce.250ms="search" placeholder="Buscar" autofocus/>
    </div>

    {{-- listado de sagas --}}
    <div class="space-y-2">
        @foreach ($this->queryDcategories() as $item)
            <flux:badge rounded color="amber">
                <a href="{{ route('diaries.index', ['cat' => $item->uuid]) }}">
                    <span class="text-xs">#{{ $item->name }}</span>
                </a>
                <div class="flex items-center justify-center ml-5">
                        <a href="{{ route('dcategories.edit', ['dcategoryUuid' => $item->uuid]) }}"><flux:button size="xs" variant="ghost" icon="pencil-square"></flux:button></a>
                        <a><flux:button size="xs" variant="ghost" icon="trash" wire:confirm="Quiere eliminar?" wire:click="deleteItem('{{ $item->uuid }}')"></flux:button></a>
                </div>
            </flux:badge>
        @endforeach
    </div>

    {{-- paginacion --}}
    <div class="mt-3">
        {{ $this->queryDcategories()->links() }}
    </div>

    {{-- exportacion e importacion de excel --}}
    <flux:separator class="mb-2 mt-10" variant="subtle" />
    <div class="flex justify-between items-center gap-1">
        <flux:button icon="cloud-arrow-down" class="text-xs text-center" wire:click="export('dcategories')">Exp.</flux:button>
        <div class="flex gap-3">
            <flux:button icon="cloud-arrow-up" class="text-xs text-center" wire:click="import('dcategories')">Imp.</flux:button>
            <flux:input type="file" wire:model="file" />
        </div>
    </div>

</div>