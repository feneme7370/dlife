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

    use \App\Traits\SortTitle;

    public function mount(){
        $this->sortFieldSelected('created_at');
    }

    //////////////////////////////////////////////////////////////////// PROPIEDADES
    // propiedades de item y titulos
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
};
?>

<div>
     {{-- titulo, descripcion y breadcrumbs --}}
    <x-page.partials.title-page 
        :title="$this->titlePage"
        :create-route="'books.create'"
        :breadcrumbs="[
            ['label' => 'Dashboard', 'route' => 'dashboard'],
            ['label' => $this->titlePage]
            ]"
    />
    <flux:badge color="pink"><a href="{{ route('books_library.index') }}">Libreria</a></flux:badge>
    <flux:badge color="violet"><a href="{{ route('books_data.index') }}">Estadisticas</a></flux:badge>
    <flux:badge color="purple"><a href="{{ route('books_incomplete.index') }}">Pendientes</a></flux:badge>

    {{-- toast de mensaje --}}
    <x-libraries.flux.toast-success />

    {{-- barra de busqueda --}}
    <x-page.partials.input-search />

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
                            {{ $item->reads->whereNotNull('end_read')->first() ? '✅' : ''}}
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
    <livewire:pages::page.partials.export-excel-complete 
        table_export="Books"
        table_import="Books"
        name_file_export="books"
        route_redirect_after_import="books.index"
    />
    
    <div class="flex justify-between items-center gap-1 mt-3">
        <flux:button icon="document" class="text-xs text-center" wire:click="exportBooksPdf()">PDF</flux:button>
    </div>

</div>