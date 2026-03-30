<?php

use App\Models\Page\Blog;
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
    public $search = '', $sortField = 'updated_at', $sortDirection = 'desc', $perPage = 10000;
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
    public $titlePage = 'Blogs';
    public $subtitlePage = 'Listado de blogs';

    //////////////////////////////////////////////////////////////////// CONSULTA DE LISTADO Y ELIMINAR ITEM
    // consulta de item
    public function queryBlogs(){
        return Blog::where('user_id', Auth::id())
            ->where('entry_type', 'blog')
            ->where(function ($query) {
                $query->where('title', 'like', "%{$this->search}%")
                      ->orWhere('slug', 'like', "%{$this->search}%");
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);
    }
    // eliminar item
    public function deleteItem($uuid){
        $item = Blog::where('user_id', Auth::id())->where('uuid', $uuid)->first();
        $item->delete();
    }
};
?>

<div>
    {{-- titulo, descripcion y breadcrumbs --}}
    <x-page.partials.title-page 
        :title="$this->titlePage"
        :create-route="'blogs.create'"
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
        @foreach ($this->queryBlogs() as $item)
            <div class="flex items-center justify-between">

                <div class="flex items-center gap-3">
                    <x-libraries.img-tumb-lightbox 
                        :uri="$item->cover_image_url ? $item->cover_image_url : asset('images/placeholderBook.jpg')" 
                        album="Portadas"
                        class_w_h="h-auto w-9"
                        class="w-10"
                    />
                    <p><a class="hover:underline" href="{{ route('blogs.show', ['blogUuid' => $item->uuid]) }}">{{ $item->title }}</p></a>
                </div>

                <div class="flex items-center justify-center">
                        <a href="{{ route('blogs.edit', ['blogUuid' => $item->uuid]) }}"><flux:button size="xs" variant="ghost" icon="pencil-square"></flux:button></a>
                        <a><flux:button size="xs" variant="ghost" icon="trash" wire:confirm="Quiere eliminar?" wire:click="deleteItem('{{ $item->uuid }}')"></flux:button></a>
                </div>

            </div>
        @endforeach
    </div>

    {{-- paginacion --}}
    <div class="mt-3">
        {{ $this->queryBlogs()->links() }}
    </div>

    {{-- exportacion e importacion de excel --}}
    <livewire:pages::page.partials.export-excel-complete 
        table_export="Blogs"
        table_import="Blogs"
        name_file_export="blogsandbullets"
        route_redirect_after_import="blogs.index"
    />

</div>