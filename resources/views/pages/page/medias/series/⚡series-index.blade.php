<?php

use App\Models\Page\Serie;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

new class extends Component
{
    use WithFileUploads;
    use WithPagination;

    //////////////////////////////////////////////////////////////////// PROPIEDADES PRINCIPALES
    // propiedades para paginacion y orden, actualizar al buscar
    public $search = '', $sortField = 'created_at', $sortDirection = 'desc', $perPage = 10000;
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
    public $titlePage = 'Series';
    public $subtitlePage = 'Listado de peliculas';

    //////////////////////////////////////////////////////////////////// CONSULTA DE LISTADO Y ELIMINAR ITEM
    // consulta de item
    public function querySeries(){
        return Serie::where('user_id', Auth::id())
            ->with(['subjects', 'genres', 'collections', 'views', 'tags'])
            ->where(function ($query) {
                $query->where('title', 'like', "%{$this->search}%")
                      ->orWhere('slug', 'like', "%{$this->search}%");
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);
    }
    // eliminar item
    public function deleteItem($codigo){
        $item = Serie::where('user_id', Auth::id())->where('uuid', $codigo)->first();
        $item->delete();
    }

    //////////////////////////////////////////////////////////////////// EXPORTAR E IMPORTAR EXCEL
    // exportar tabla cruda a excel
    public function exportComplete(){
        return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\SeriesExport, 'series_info.xlsx');
    }

    // importar tabla cruda de excel
    public function importComplete(){
        $this->validate(['file' => 'required|mimes:xlsx,csv']);
        \Maatwebsite\Excel\Facades\Excel::import(new \App\Imports\SeriesImport, $this->file);
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
                <a href="{{ route('series.create') }}"><flux:button size="xs" variant="ghost" icon="plus"></flux:button></a>
                {{ $this->titlePage }}
            </flux:heading>
            <flux:text class="text-base">{{ $this->subtitlePage }}</flux:text>
    
            <flux:breadcrumbs>
                <flux:breadcrumbs.item href="{{ route('dashboard') }}">Dashboard</flux:breadcrumbs.item>
                <flux:breadcrumbs.item>{{ $this->titlePage }}</flux:breadcrumbs.item>
            </flux:breadcrumbs>
    
            <flux:separator variant="subtle" />

            <flux:badge color="pink"><a href="{{ route('series_library.index') }}">Estanteria</a></flux:badge>
            <flux:badge color="violet"><a href="{{ route('series_data.index') }}">Estadisticas</a></flux:badge>
            {{-- <flux:badge color="purple"><a href="{{ route('series_incomplete.index') }}">Pendientes</a></flux:badge> --}}
            <flux:badge color="fuchsia"><a href="{{ route('mgenres.index') }}">Generos</a></flux:badge>
            <flux:badge color="fuchsia"><a href="{{ route('mtags.index') }}">Etiquetas</a></flux:badge>
        </div>
    </div>

    {{-- toast de mensaje --}}
    <x-libraries.flux.toast-success />
    
    {{-- buscador --}}
    <div class="mb-3">
        <flux:input type="text" label="Buscar" wire:model.live.debounce.250ms="search" placeholder="Buscar" autofocus/>
    </div>

    {{-- listado de libros --}}
    <div class="space-y-2">
        @foreach ($this->querySeries() as $item)
            <div class="flex items-center justify-between">

                <div class="flex flex-wrap items-center gap-3">
                    <x-libraries.img-tumb-lightbox 
                        :uri="$item->cover_image_url" 
                        album="Portadas"
                        class_w_h="h-12 w-9"
                    />

                    <p><a class="hover:underline" href="{{ route('series.show', ['serieUuid' => $item->uuid]) }}">{{ $item->title }}</p></a>
                    <p class="text-xs italic text-gray-700 dark:text-gray-300">
                        ({{ $item->start_date .' / ' }} {{ $item->end_date ?? 'En emision' }}) - 
                        {{ $item->is_favorite ? '❤️' : ''}}
                        {{ $item->is_abandonated ? '🚫' : ''}}
                        {{ $item->summary_clear ? '🗒️' : ''}}
                        {{ $item->notes_clear ? '✍️' : ''}}
                        {{ $item->views->whereNotNull('end_view')->first() ? '✅' : '' }}
                        {{ $item->tags->count() ? '#️⃣'.$item->tags->count() : ''}}
                    </p>
                </div>

                <div class="flex items-center justify-center">
                        <a href="{{ route('series.edit', ['serieUuid' => $item->uuid]) }}"><flux:button size="xs" variant="ghost" icon="pencil-square"></flux:button></a>
                        <a><flux:button size="xs" variant="ghost" icon="trash" wire:confirm="Quiere eliminar?" wire:click="deleteItem('{{ $item->uuid }}')"></flux:button></a>
                </div>

            </div>
        @endforeach
    </div>

    {{-- paginacion --}}
    <div class="mt-3">
        {{ $this->querySeries()->links() }}
    </div>

    {{-- exportacion e importacion de excel --}}
    <flux:separator class="mb-2 mt-10" variant="subtle" />

    <div class="flex justify-between items-center gap-1">
            <flux:button icon="cloud-arrow-down" class="text-xs text-center" wire:click="exportComplete()">Exp. Series</flux:button>
            <div class="flex gap-3">
            <flux:button icon="cloud-arrow-up" class="text-xs text-center" wire:click="importComplete()">Imp. Series</flux:button>
            <flux:input type="file" wire:model="file" />
            </div>
    </div>

</div>