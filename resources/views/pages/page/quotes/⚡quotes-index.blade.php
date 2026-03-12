<?php

use App\Models\Page\Quote;
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
    public $search = '', $sortField = 'created_at', $sortDirection = 'asc', $perPage = 10000;
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
    public $file;
    public $titlePage = 'Frases';
    public $subtitlePage = 'Listado de frases';

    //////////////////////////////////////////////////////////////////// CONSULTA DE LISTADO Y ELIMINAR ITEM
    // consulta de item
    public function queryQuotes(){
        return Quote::where('user_id', Auth::id())
            ->where(function ($query) {
                $query->where('content', 'like', "%{$this->search}%")
                      ->orWhere('author', 'like', "%{$this->search}%")
                      ->orWhere('source', 'like', "%{$this->search}%");
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);
    }
    // eliminar item
    public function deleteItem($uuid){
        $item = Quote::where('user_id', Auth::id())->where('uuid', $uuid)->first();
        $item->delete();
    }

    //////////////////////////////////////////////////////////////////// EXPORTAR E IMPORTAR EXCEL
    // exportar tabla cruda a excel
    public function exportComplete(){
        return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\QuotesExport,"quotes_info.xlsx");
    }
    // importar tabla cruda de excel
    public function importComplete(){
        $this->validate(['file' => 'required|mimes:xlsx,csv']);
        \Maatwebsite\Excel\Facades\Excel::import(new \App\Imports\QuotesImport, $this->file);
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
                <a href="{{ route('quotes.create') }}"><flux:button size="xs" variant="ghost" icon="plus"></flux:button></a>
                {{ $this->titlePage }}
            </flux:heading>
            <flux:text class="text-base">{{ $this->subtitlePage }}</flux:text>
    
            <flux:breadcrumbs>
                <flux:breadcrumbs.item href="{{ route('dashboard') }}">Dashboard</flux:breadcrumbs.item>
                <flux:breadcrumbs.item>{{ $this->titlePage }}</flux:breadcrumbs.item>
            </flux:breadcrumbs>
    
            <flux:separator variant="subtle" />
        </div>
    </div>

    {{-- toast de mensaje --}}
    <x-libraries.flux.toast-success />

    {{-- buscador --}}
    <div class="mb-3">
        <flux:input type="text" label="Buscar" wire:model.live.debounce.250ms="search" placeholder="Buscar"/>
    </div>

    {{-- listado de frases --}}
    <div class="space-y-3">
        @foreach ($this->queryQuotes() as $item)

            <div class="w-10/12 mx-auto">
                <flux:separator  />
            </div>

            <div class="flex items-center justify-between">

                <div class="flex flex-col justify-items-center items-start gap-1">
                    <p class="text-gray-800 dark:text-gray-100 text-sm">{{ $item->content }}</p>
                    <p class="ml-5 text-sm italic text-gray-600 dark:text-gray-300">
                        {{ $item->author ? ' -- '. $item->author : '' }}
                        {{ $item->source ? ' | '. $item->source : '' }}
                    </p>
                </div>

                <div class="flex items-center justify-center">
                        <a 
                            href="{{ route('quotes.edit', ['quoteUuid' => $item->uuid]) }}"
                        ><flux:button size="xs" variant="ghost" icon="pencil-square"></flux:button></a>
                        <a><flux:button size="xs" variant="ghost" icon="trash" wire:confirm="Quiere eliminar?" wire:click="deleteItem('{{ $item->uuid }}')"></flux:button></a>
                </div>

            </div>
        @endforeach
    </div>

    {{-- paginacion --}}
    <div class="mt-3">
        {{ $this->queryQuotes()->links() }}
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