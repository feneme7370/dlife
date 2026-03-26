<?php

use Livewire\Component;

new class extends Component
{
    use \Livewire\WithPagination;

    use \App\Traits\SortTitle;
    use \App\Traits\QueryStrings;

    public $name_collection;
    public $name_subject;
    public $name_genre;
    public $name_star;

    //////////////////////////////////////////////////////////////////// PROPIEDADES
    // propiedades de item y titulos
    public $books = [];
    public $titlePage = 'Libreria';
    public $subtitlePage = 'Listado de libros leidos';
    public $status_read, $collection_selected, $subject_selected, $genre_selected, $star_selected;

    //////////////////////////////////////////////////////////////////// PRE CARGAR DATOS
    public function mount(){
        $books = $this->booksQuery()->get();

        $this->books = collect($books)
            ->groupBy(function ($book) {
                return $book->reads_max_end_read
                    ? \Carbon\Carbon::parse($book->reads_max_end_read)->format('Y')
                    : 'Sin fecha';
            })
            ->sortKeysDesc();
        

        $this->name_collection = $this->collection_selected ? \App\Models\Page\Collection::where('uuid', $this->collection_selected)->first()->name : null;
        $this->name_subject = $this->subject_selected ? \App\Models\Page\Subject::where('uuid', $this->subject_selected)->first()->name : null;
        $this->name_genre = $this->genre_selected ? \App\Models\Page\Genre::where('uuid', $this->genre_selected)->first()->name : null;
        $this->name_star = $this->star_selected ? str_repeat('★', $this->star_selected) : null;
    }

    //////////////////////////////////////////////////////////////////// CONSULTA DE LISTADO Y ELIMINAR ITEM
    public function booksQuery(){
        return \App\Models\Page\Book::where('user_id', \Illuminate\Support\Facades\Auth::id())
        
            // no abandonado
            ->whereHas('reads')
            ->withMax('reads', 'end_read')
            ->whereHas('reads', fn($q) => $q->where('end_read', '<>' ,''))

            ->when($this->star_selected !== null, function( $query) {
                return $query->where('rating', $this->star_selected);
            })
            ->when($this->subject_selected, function ($query) {
                $query->whereHas('subjects', function ($q) {
                    $q->where('subjects.uuid', $this->subject_selected);
                });
            })
            ->when($this->genre_selected, function ($query) {
                $query->whereHas('genres', function ($q) {
                    $q->where('genres.uuid', $this->genre_selected);
                });
            })
            ->when($this->collection_selected, function ($query) {
                $query->whereHas('collections', function ($q) {
                    $q->where('collections.uuid', $this->collection_selected);
                });
            })

            ->orderBy('reads_max_end_read', $this->sortDirection);
    }
};
?>

<div>
     {{-- titulo, descripcion y breadcrumbs --}}
    <x-page.partials.title-page 
        :title="$this->titlePage"
        :create-route="'books.index'"
        icon="arrow-uturn-left"
        :breadcrumbs="[
            ['label' => 'Dashboard', 'route' => 'dashboard'],
            ['label' => 'Libros', 'route' => 'books.index'],
            ['label' => $this->titlePage]
        ]"
    />

     {{-- toast de mensaje --}}
     <x-libraries.flux.toast-success />
     
    {{-- titulos de filtros --}}
    <div>
        <p class="text-start text-lg sm:text-2xl font-bold mb-3">
            @if ($genre_selected || $subject_selected || $collection_selected || $star_selected)
                {{ $this->name_genre ? 'Genero: '.$this->name_genre : null }}
                {{ $this->name_subject ? 'Autor: '.$this->name_subject : null }}
                {{ $this->name_collection ? 'Coleccion: '.$this->name_collection : null }}
                {{ $this->name_star ? 'Estrellas: '.$this->name_star : null }}
            @endif
        </p>
    </div>

    {{-- cuadricula --}}
    <div class="relative shadow-md sm:rounded-lg">
        
        @foreach ($books as $year => $books_by_year)
        
            <p class="text-center text-lg sm:text-2xl font-bold mb-3">{{ $year }}</p>
            
            <div class="flex flex-wrap justify-center gap-1 px-1 py-3">
                @foreach ($books_by_year as $item)
                    <a 
                        href="{{ route('books.show', ['bookUuid' => $item->uuid]) }}"
                    >
                        <div class="relative w-20 h-32 sm:w-40 sm:h-60 rounded-lg overflow-hidden shadow-lg group">
                            <img src="{{ $item->cover_image_url }}" alt="Portada del libro" class="w-full h-full object-cover">
                            <div class="absolute inset-0 bg-black bg-opacity-50 opacity-0 group-hover:opacity-100 flex items-center justify-center transition-opacity duration-300">
                                <div class="text-center space-y-10">
                                    <p class="text-white text-lg font-semibold px-2 text-center">{{ $item->title }}</p>
                                    <p class="relative text-xs italic text-gray-700 dark:text-gray-300">
                                        {{ $item->is_favorite ? '❤️' : ''}}
                                        {{ $item->is_abandonated ? '🚫' : ''}}
                                        {{ $item->summary_clear ? '🗒️' : ''}}
                                        {{ $item->notes_clear ? '✍️' : ''}}
                                        {{ $item->reads->whereNotNull('end_read') ? '✅' : ''}}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </a>

                @endforeach
            </div>

        @endforeach

            
    </div>
</div>