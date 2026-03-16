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
    public $file;
    public $books;
    public $titlePage = 'Libros';
    public $subtitlePage = 'Listado de libros';

    //////////////////////////////////////////////////////////////////// CONSULTA DE LISTADO Y ELIMINAR ITEM
    // consulta de item
    public function queryBooks(){
        return Book::where('user_id', Auth::id())
            ->with(['subjects', 'genres', 'collections', 'reads', 'tags'])
            ->where(function ($query) {
                $query->where('title', 'like', "%{$this->search}%")
                      ->orWhere('slug', 'like', "%{$this->search}%");
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);
    }
    // eliminar item
    public function deleteItem($uuid){
        $item = Book::where('user_id', Auth::id())->where('uuid', $uuid)->first();
        $item->delete();
    }

    //////////////////////////////////////////////////////////////////// EXPORTAR PDF
    // exportar pdf
    // public function exportBooksPdf()
    // {
    //     $books = $this->queryBooks();

    //     $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('exports.books-pdf', [
    //         'books' => $books
    //     ]);

    //     return $pdf->download('books.pdf');
    // }
    public function exportBooksPdf()
    {
        $books = $this->queryBooks();

        $html = view('exports.books-pdf', [
            'books' => $books
        ])->render();

        $pdf = \Spatie\Browsershot\Browsershot::html($html)
            ->format('A4')
            ->margins(10,10,10,10)
            ->pdf();

        return response()->streamDownload(
            fn() => print($pdf),
            "books_library.pdf"
        );
    }


    //////////////////////////////////////////////////////////////////// EXPORTAR E IMPORTAR EXCEL
    // exportar tabla cruda a excel
    public function exportComplete(){
        return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\BooksExport, 'books_info.xlsx');
    }

    // importar tabla cruda de excel
    public function importComplete(){
        $this->validate(['file' => 'required|mimes:xlsx,csv']);
        \Maatwebsite\Excel\Facades\Excel::import(new \App\Imports\BooksImport, $this->file);
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
                <a href="{{ route('books.create') }}"><flux:button size="xs" variant="ghost" icon="plus"></flux:button></a>
                {{ $this->titlePage }}
            </flux:heading>
            <flux:text class="text-base">{{ $this->subtitlePage }}</flux:text>
    
            <flux:breadcrumbs>
                <flux:breadcrumbs.item href="{{ route('dashboard') }}">Dashboard</flux:breadcrumbs.item>
                <flux:breadcrumbs.item>{{ $this->titlePage }}</flux:breadcrumbs.item>
            </flux:breadcrumbs>
    
            <flux:separator variant="subtle" />

            <flux:badge color="pink"><a href="{{ route('books_library.index') }}">Libreria</a></flux:badge>
            <flux:badge color="violet"><a href="{{ route('books_data.index') }}">Estadisticas</a></flux:badge>
            <flux:badge color="purple"><a href="{{ route('books_incomplete.index') }}">Pendientes</a></flux:badge>
            <flux:badge color="fuchsia"><a href="{{ route('book-genres.index') }}">Generos</a></flux:badge>
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
        @foreach ($this->queryBooks() as $item)
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
                        <p><a class="hover:underline text-sm font-medium" href="{{ route('books.show', ['bookUuid' => $item->uuid]) }}">{{ $item->title }}</p></a>
                        <p class="text-xs italic text-gray-700 dark:text-gray-300">
                            ({{ $item->release_date }}) - 
                            {{ $item->pages }} pags. | 
                            {{ $item->is_favorite ? '❤️' : ''}}
                            {{ $item->is_abandonated ? '🚫' : ''}}
                            {{ $item->summary_clear ? '🗒️' : ''}}
                            {{ $item->notes_clear ? '✍️' : ''}}
                            {{ $item->reads->first() ? '✅' : ''}}
                            {{ $item->tags->count() ? '#️⃣'.$item->tags->count() : ''}}
                        </p>
                    </div>
                </div>

                <div class="col-span-2 flex items-center justify-center">
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
        <div class="flex gap-1">
            <flux:button icon="cloud-arrow-down" class="text-xs text-center" wire:click="exportComplete()">Excel</flux:button>
            <flux:button icon="document" class="text-xs text-center" wire:click="exportBooksPdf()">PDF</flux:button>
        </div>
        <div class="flex gap-3">
            <flux:button icon="cloud-arrow-up" class="text-xs text-center" wire:click="importComplete()">Imp. Libros</flux:button>
            <flux:input type="file" wire:model="file" />
        </div>
    </div>

</div>