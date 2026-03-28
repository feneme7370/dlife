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
    use \App\Traits\SortTitle;

    //////////////////////////////////////////////////////////////////// PRE CARGAR DATOS
    public function mount(){
        $this->sortFieldSelected('created_at');
    }

    //////////////////////////////////////////////////////////////////// PROPIEDADES
    // propiedades de item y titulos
    public $titlePage = 'Frases';
    public $subtitlePage = 'Listado de frases';

    //////////////////////////////////////////////////////////////////// CONSULTA DE LISTADO Y ELIMINAR ITEM
    // consulta de item
    public function querySearch(){
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
};
?>

<div>
     {{-- titulo, descripcion y breadcrumbs --}}
    <x-page.partials.title-page 
        :title="$this->titlePage"
        :create-route="'quotes.create'"
        :breadcrumbs="[
            ['label' => 'Dashboard', 'route' => 'dashboard'],
            ['label' => $this->titlePage]
        ]"
    />

     {{-- toast de mensaje --}}
     <x-libraries.flux.toast-success />

    {{-- barra de busqueda --}}
    <x-page.partials.input-search />

    {{-- listado de frases --}}
    <div class="space-y-3">
        @foreach ($this->querySearch() as $item)

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
        {{ $this->querySearch()->links() }}
    </div>

    {{-- exportacion e importacion de excel --}}
    <livewire:pages::page.partials.export-excel-complete 
        table_export="Quotes"
        table_import="Quotes"
        name_file_export="quotes"
        route_redirect_after_import="quotes.index"
    />

</div>