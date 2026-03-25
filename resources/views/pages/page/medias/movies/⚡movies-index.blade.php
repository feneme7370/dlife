<?php

use App\Models\Page\Movie;
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

    //////////////////////////////////////////////////////////////////// PROPIEDADES
    // propiedades de item y titulos
    public $titlePage = 'Peliculas';
    public $subtitlePage = 'Listado de peliculas';

    //////////////////////////////////////////////////////////////////// CONSULTA DE LISTADO Y ELIMINAR ITEM
    // consulta de item
    public function queryMovies(){
        return Movie::where('user_id', Auth::id())
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
        $item = Movie::where('user_id', Auth::id())->where('uuid', $codigo)->first();
        $item->delete();
    }
};
?>

<div>
     {{-- titulo, descripcion y breadcrumbs --}}
    <x-page.partials.title-page 
        :title="$this->titlePage"
        :create-route="'movies.create'"
        :breadcrumbs="[
            ['label' => 'Dashboard', 'route' => 'dashboard'],
            ['label' => $this->titlePage]
        ]"
    />

     {{-- toast de mensaje --}}
     <x-libraries.flux.toast-success />

    <flux:badge color="pink"><a href="{{ route('movies_library.index') }}">Estanteria</a></flux:badge>
    <flux:badge color="violet"><a href="{{ route('movies_data.index') }}">Estadisticas</a></flux:badge>

    {{-- toast de mensaje --}}
    <x-libraries.flux.toast-success />
  
    {{-- barra de busqueda --}}
    <x-page.partials.input-search />

    {{-- listado de libros --}}
    <div class="space-y-2">
        @foreach ($this->queryMovies() as $item)
            <div class="grid grid-cols-12 gap-1 items-start justify-center">

                <div class="col-span-10 flex gap-1">
                    <div>
                        <x-libraries.img-tumb-lightbox 
                           :uri="$item->cover_image_url" 
                           album="Portadas"
                           class_w_h="h-12 w-9"
                       />
                    </div>

                    <div>
                        <p><a class="hover:underline text-sm font-medium" href="{{ route('movies.show', ['movieUuid' => $item->uuid]) }}">{{ $item->title }}</p></a>
                        <p class="text-xs italic text-gray-700 dark:text-gray-300">
                            ({{ $item->release_date }}) - 
                            {{ $item->runtime }} mins. | 
                            {{ $item->is_favorite ? '❤️' : ''}}
                            {{ $item->is_abandonated ? '🚫' : ''}}
                            {{ $item->summary_clear ? '🗒️' : ''}}
                            {{ $item->notes_clear ? '✍️' : ''}}
                            {{ $item->views->whereNotNull('end_view')->first() ? '✅' : '' }}
                            {{ $item->tags->count() ? '#️⃣'.$item->tags->count() : ''}}
                        </p>
                    </div>
                </div>

                <div class="col-span-2 flex items-center justify-center">
                        <a href="{{ route('movies.edit', ['movieUuid' => $item->uuid]) }}"><flux:button size="xs" variant="ghost" icon="pencil-square"></flux:button></a>
                        <a><flux:button size="xs" variant="ghost" icon="trash" wire:confirm="Quiere eliminar?" wire:click="deleteItem('{{ $item->uuid }}')"></flux:button></a>
                </div>

            </div>
        @endforeach
    </div>

    {{-- paginacion --}}
    <div class="mt-3">
        {{ $this->queryMovies()->links() }}
    </div>

    {{-- exportacion e importacion de excel --}}
    <livewire:pages::page.partials.export-excel-complete 
        table_export="Movies"
        table_import="Movies"
        name_file_export="movies"
        route_redirect_after_import="movies.index"
    />

</div>