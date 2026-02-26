<?php

use App\Models\Page\Book;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

new class extends Component
{
    use WithFileUploads;
    use WithPagination;

    // propiedades para paginacion y orden, actualizar al buscar
    public $search = '', $sortField = 'title', $sortDirection = 'asc', $perPage = 10000;
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
    public $books;
    public $title = 'Libros';
    public $subtitle = 'Listado de libros';

    // consulta de item
    public function queryBooks(){
        return Book::where('user_id', Auth::id())
            ->with(['book_subjects', 'book_book_genres', 'book_collections', 'book_reads'])
            ->where(function ($query) {
                $query->where('title', 'like', "%{$this->search}%")
                      ->orWhere('slug', 'like', "%{$this->search}%");
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);
    }

    // eliminar item
    public function deleteItem($codigo){
        $item = Book::where('user_id', Auth::id())->where('uuid', $codigo)->first();
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
        <flux:main container class="mb-1 space-y-1">
            <flux:heading size="xl" level="1">
                <a href="{{ route('books.create') }}"><flux:button size="xs" variant="ghost" icon="plus"></flux:button></a>
                {{ $this->title }}
            </flux:heading>
            <flux:text class="text-base">{{ $this->subtitle }}</flux:text>
    
            <flux:breadcrumbs>
                <flux:breadcrumbs.item href="{{ route('dashboard') }}">Dashboard</flux:breadcrumbs.item>
                <flux:breadcrumbs.item>{{ $this->title }}</flux:breadcrumbs.item>
            </flux:breadcrumbs>
    
            <flux:separator variant="subtle" />
        </flux:main>
    </div>

    {{-- buscador --}}
    <div class="mb-3">
        <flux:input type="text" label="Buscar" wire:model.live.debounce.250ms="search" placeholder="Buscar" autofocus/>
    </div>

    {{-- listado de libros --}}
    <div class="space-y-2">
        @foreach ($this->queryBooks() as $item)
            <div class="flex items-center justify-between">

                <div class="flex flex-wrap items-center gap-3">
                    <img src="{{ $item->cover_image_url }}" class="h-12 object-cover rounded-sm" alt="">
                    <p><a class="hover:underline" href="{{ route('books.show', ['bookUuid' => $item->uuid]) }}">{{ $item->title }}</p></a>
                    {{-- <flux:badge rounded icon="user" as="button" color="violet" size="sm">
                        @foreach ($item->book_subjects as $subject)
                            <a href="#" class="hover:underline mr-1">{{ $subject->name }}</a>
                        @endforeach
                    </flux:badge>
                    <span class="text-xs text-gray-800">
                        @foreach ($item->book_collections as $collection)
                            <flux:badge rounded icon="numbered-list" as="button" color="purple" size="sm">
                                <a href="#" class="hover:underline">{{ $collection->name }}</a>
                            </flux:badge>
                        @endforeach
                    </span>
                    <span class="text-xs text-gray-800">
                        @foreach ($item->book_book_genres as $book_genre)
                            <flux:badge rounded icon="list-bullet" as="button" color="fuchsia" size="sm">
                                <a href="#" class="hover:underline">{{ $book_genre->name_general }} / {{ $book_genre->name }}</a>
                            </flux:badge>
                        @endforeach
                    </span> --}}
                    <p class="text-xs italic text-gray-700 dark:text-gray-300">
                        ({{ $item->release_date }}) - 
                        {{ $item->pages }} pags. | 
                        {{ $item->is_favorite ? '❤️' : ''}}
                        {{ $item->is_abandonated ? '🚫' : ''}}
                        {{ $item->summary_clear ? '🗒️' : ''}}
                        {{ $item->notes_clear ? '✍️' : ''}}
                        {{ $item->book_reads->first() ? '✅' : ''}}
                    </p>
                </div>

                <div class="flex items-center justify-center">
                        <a href="{{ route('books.edit', ['bookUuid' => $item->uuid]) }}"><flux:button size="xs" variant="ghost" icon="pencil-square"></flux:button></a>
                        <a><flux:button size="xs" variant="ghost" icon="trash" wire:confirm="Quiere eliminar?" wire:click="deleteItem('{{ $item->uuid }}')"></flux:button></a>
                </div>

            </div>
        @endforeach
    </div>

    {{-- paginacion --}}
    <div class="mt-3">
        {{ $this->queryBooks()->links() }}
    </div>

    {{-- exportacion e importacion de excel --}}
    <flux:separator class="mb-2 mt-10" variant="subtle" />
    <div class="flex justify-between items-center gap-1">
        <flux:button icon="cloud-arrow-down" class="text-xs text-center" wire:click="export('books')">Exp.</flux:button>
        <div class="flex gap-3">
            <flux:button icon="cloud-arrow-up" class="text-xs text-center" wire:click="import('books')">Imp.</flux:button>
            <flux:input type="file" wire:model="file" />
        </div>
    </div>

</div>