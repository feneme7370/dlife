<?php

use App\Models\Page\Game;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

new class extends Component
{
    use WithFileUploads;
    use WithPagination;
    use \App\Traits\SortTitle;

    public function mount(){
        $this->sortFieldSelected('created_at');
    }

    //////////////////////////////////////////////////////////////////// PROPIEDADES
    // propiedades de item y titulos
    public $titlePage = 'Juegos';
    public $subtitlePage = 'Listado de juegos';

    //////////////////////////////////////////////////////////////////// CONSULTA DE LISTADO Y ELIMINAR ITEM
    // consulta de item
    public function querySearch(){
        return Game::where('user_id', Auth::id())
            ->with(['subjects', 'categories', 'collections', 'playeds', 'tags'])
            ->where(function ($query) {
                $query->where('title', 'like', "%{$this->search}%")
                      ->orWhere('slug', 'like', "%{$this->search}%");
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);
    }
    // eliminar item
    public function deleteItem($uuid){
        $item = Game::where('user_id', Auth::id())->where('uuid', $uuid)->first();
        $item->delete();
    }
};
?>

<div>
     {{-- titulo, descripcion y breadcrumbs --}}
    <x-page.partials.title-page 
        :title="$this->titlePage"
        :create-route="'games.create'"
        :breadcrumbs="[
            ['label' => 'Dashboard', 'route' => 'dashboard'],
            ['label' => $this->titlePage]
        ]"
    />

     {{-- toast de mensaje --}}
     <x-libraries.flux.toast-success />

    <flux:badge color="pink"><a href="{{ route('games_library.index') }}">Estanteria</a></flux:badge>
    <flux:badge color="violet"><a href="{{ route('games_data.index') }}">Estadisticas</a></flux:badge>

    {{-- toast de mensaje --}}
    <x-libraries.flux.toast-success />
  
    {{-- barra de busqueda --}}
    <x-page.partials.input-search />

    {{-- listado de libros --}}
    <div class="space-y-2">
        @foreach ($this->querySearch() as $item)
            <div class="grid grid-cols-12 gap-1 items-start justify-center">

                <div class="col-span-10 flex gap-1">
                    <div>
                        <x-libraries.img-tumb-lightbox 
                            :uri="$item->cover_image_url ? $item->cover_image_url : asset('images/placeholderVisual.jpg')" 
                           album="Portadas"
                           class_w_h="h-12 w-9"
                       />
                    </div>

                    <div>
                        <p><a class="hover:underline text-sm font-medium" href="{{ route('games.show', ['gameUuid' => $item->uuid]) }}">{{ $item->title }}</p></a>
                        <p class="text-xs italic text-gray-700 dark:text-gray-300">
                            ({{ $item->release_date }}) - 
                            {{ $item->is_favorite ? '❤️' : ''}}
                            {{ $item->is_abandonated ? '🚫' : ''}}
                            {{ $item->summary_clear ? '🗒️' : ''}}
                            {{ $item->notes_clear ? '✍️' : ''}}
                            {{ $item->playeds->whereNotNull('end_played')->first() ? '✅' : '' }}
                            {{ $item->tags->count() ? '#️⃣'.$item->tags->count() : ''}}
                        </p>
                    </div>
                </div>

                <div class="col-span-2 flex items-center justify-center">
                        <a href="{{ route('games.edit', ['gameUuid' => $item->uuid]) }}"><flux:button size="xs" variant="ghost" icon="pencil-square"></flux:button></a>
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
        table_export="Games"
        table_import="Games"
        name_file_export="games"
        route_redirect_after_import="games.index"
    />

</div>