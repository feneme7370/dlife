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
    use \App\Traits\SortTitle;
    
    public function mount(){
        $this->sortFieldSelected('created_at');
    }

    // propiedades de item y titulos
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
};
?>

<div>
     {{-- titulo, descripcion y breadcrumbs --}}
    <x-page.partials.title-page 
        :title="$this->titlePage"
        :create-route="'series.create'"
        :breadcrumbs="[
            ['label' => 'Dashboard', 'route' => 'dashboard'],
            ['label' => $this->titlePage]
        ]"
    />

     {{-- toast de mensaje --}}
     <x-libraries.flux.toast-success />

    <flux:badge color="pink"><a href="{{ route('series_library.index') }}">Estanteria</a></flux:badge>
    <flux:badge color="violet"><a href="{{ route('series_data.index') }}">Estadisticas</a></flux:badge>

    {{-- toast de mensaje --}}
    <x-libraries.flux.toast-success />
    
    {{-- barra de busqueda --}}
    <x-page.partials.input-search />

    {{-- listado de libros --}}
    <div class="space-y-2">
        @foreach ($this->querySeries() as $item)
            <div class="grid grid-cols-12 gap-1 items-start justify-center">

                <div class="col-span-10 flex gap-1">
                    <div>
                        <x-libraries.img-tumb-lightbox 
                            :uri="$item->cover_image_url" 
                            album="Portadas"
                            class_w_h="h-auto w-9"
                            class="w-10"
                        />
                    </div>

                    <div>
                        <p><a class="hover:underline text-sm font-medium" href="{{ route('series.show', ['serieUuid' => $item->uuid]) }}">{{ $item->title }}</p></a>
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
                </div>

                <div class="col-span-2 flex items-center justify-center">
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
    <livewire:pages::page.partials.export-excel-complete 
        table_export="Series"
        table_import="Series"
        name_file_export="series"
        route_redirect_after_import="series.index"
    />

</div>