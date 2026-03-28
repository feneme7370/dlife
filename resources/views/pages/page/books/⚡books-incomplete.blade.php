<?php

use Livewire\Component;

new class extends Component
{
    //////////////////////////////////////////////////////////////////// PROPIEDADES PRINCIPALES
    // propiedades de item y titulos
    public $books;
    public $titlePage = 'Datos pendientes';
    public $subtitlePage = 'Datos de libros incompletos';

    //////////////////////////////////////////////////////////////////// PRE CARGAR DATOS
    // cargar datos iniciales
    public function mount(){
        // Traés todos los libros del usuario (sin filtros por fecha)
        $this->books = \App\Models\Page\Book::where('user_id', \Illuminate\Support\Facades\Auth::id())
        ->orderBy('title', 'asc')
            ->with(['subjects', 'genres', 'collections', 'reads', 'tags'])
            ->get();
    }

    //////////////////////////////////////////////////////////////////// CONSULTAS
    // libros totales pendientes de leer
    public function toRead(){
        return $this->books->filter(fn($book) => !$book->reads->count() > 0);
    }

    // libros totales abandonados
    public function abandonatedBooks(){
        return $this->books->filter(fn($book) => $book->is_abandonated == true);
    }

    // libros totales pendientes de comentar
    public function toComment(){
        $s = $this->books->filter(fn($book) => !$book->notes_clear != '' || !$book->notes_clear != null);
        $s = $s->filter(fn($book) => $book->reads->where('end_read', '!=', null)->count() > 0);
        return $s;
    }

    // libros totales pendientes de comentar
    public function toPutGenre(){
        return $this->books->filter(fn($book) => $book->genres->count() < 1);
    }

    // libros totales pendientes de comentar
    public function toPutSubject(){
        return $this->books->filter(fn($book) => $book->subjects->count() < 1);
    }
    // libros totales pendientes de comentar
    public function toPutTag(){
        return $this->books->filter(fn($book) => $book->tags->count() < 1);
    }
    // libros totales pendientes de comentar
    public function toPutCollection(){
        return $this->books->filter(fn($book) => $book->collections->count() < 1);
    }

    // traer colecciones y libros leidos
    public function collectionsWithBooksRead(){
        return \App\Models\Page\Collection::where('user_id', \Illuminate\Support\Facades\Auth::id())
            ->where('books_amount', '>', 0)
            ->withCount([
                'books as books_read_count' => function ($query) {
                    $query->whereHas('reads', fn($q) => $q->whereNotNull('end_read'));
                }
            ])
            ->orderBy('name', 'asc')
            ->get();
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

    {{-- pendientes totales a leer de cualquier año --}}
    <flux:separator text="📖 Pendientes totales a leer ({{ $this->toRead()->count() }})" />
    <div class="overflow-y-scroll max-h-96">
        @foreach ($this->toRead() as $item)
            <flux:text class="mt-2">
                <a
                    class="hover:underline" 
                    href="{{ route('books.show', ['bookUuid' => $item['uuid']]) }}"
                >🗒️ {{ $item['title'] }}</a>
            </flux:text>
        @endforeach
    </div>

    {{-- pendientes de sagas --}}
    <flux:separator text="📖 Pendientes de sagas" />
    <div class="overflow-y-scroll max-h-96">
        @foreach ($this->collectionsWithBooksRead() as $item)
            <flux:text class="mt-2">
                <a
                    class="hover:underline" 
                    href="{{ route('books.show', ['bookUuid' => $item['uuid']]) }}"
                >🗒️ {{ $item['name'].' '. $item['books_read_count'].'/'. $item['books_amount'] }}</a>
            </flux:text>
        @endforeach
    </div>

    <div class="grid grid-cols-2 gap-1">
        <div>
            {{-- pentientes comentar leidos --}}
            <flux:separator text="✍️ Pendientes de comentar ({{ $this->toComment()->count() }})" />
            <div class="overflow-y-scroll max-h-96">
                @foreach ($this->toComment() as $item)
                    <flux:text class="mt-2">
                        <a
                            class="hover:underline" 
                            href="{{ route('books.show', ['bookUuid' => $item['uuid']]) }}"
                        >🗒️ {{ $item['title'] }}</a>
                    </flux:text>
                @endforeach
            </div>
        </div>
        
        <div>
            {{-- pentientes de genero --}}
            <flux:separator text="✍️ Pendientes de colocar genero ({{ $this->toPutGenre()->count() }})" />
            <div class="overflow-y-scroll max-h-96">
                @foreach ($this->toPutGenre() as $item)
                    <flux:text class="mt-2">
                        <a
                            class="hover:underline" 
                            href="{{ route('books.show', ['bookUuid' => $item['uuid']]) }}"
                        >🗒️ {{ $item['title'] }}</a>
                    </flux:text>
                @endforeach
            </div>
        </div>

        <div>
            {{-- pentientes de autor --}}
            <flux:separator text="✍️ Pendientes de colocar autor ({{ $this->toPutSubject()->count() }})" />
            <div class="overflow-y-scroll max-h-96">
                @foreach ($this->toPutSubject() as $item)
                    <flux:text class="mt-2">
                        <a
                            class="hover:underline" 
                            href="{{ route('books.show', ['bookUuid' => $item['uuid']]) }}"
                        >🗒️ {{ $item['title'] }}</a>
                    </flux:text>
                @endforeach
            </div>
        </div>

        <div>
            {{-- pentientes de colección --}}
            <flux:separator text="✍️ Pendientes de colocar colección ({{ $this->toPutCollection()->count() }})" />
            <div class="overflow-y-scroll max-h-96">
                @foreach ($this->toPutCollection() as $item)
                    <flux:text class="mt-2">
                        <a
                            class="hover:underline" 
                            href="{{ route('books.show', ['bookUuid' => $item['uuid']]) }}"
                        >🗒️ {{ $item['title'] }}</a>
                    </flux:text>
                @endforeach
            </div>
        </div>

        <div>
            {{-- pentientes de tag --}}
            <flux:separator text="✍️ Pendientes de colocar tag ({{ $this->toPutTag()->count() }})" />
            <div class="overflow-y-scroll max-h-96">
                @foreach ($this->toPutTag() as $item)
                    <flux:text class="mt-2">
                        <a
                            class="hover:underline" 
                            href="{{ route('books.show', ['bookUuid' => $item['uuid']]) }}"
                        >🗒️ {{ $item['title'] }}</a>
                    </flux:text>
                @endforeach
            </div>
        </div>
    </div>

    {{-- abandonados totales de cualquier año --}}
    <flux:separator text="🚫 Libros abandonados ({{ $this->abandonatedBooks()->count() }})" />
    <div class="overflow-y-scroll max-h-96">
        @foreach ($this->abandonatedBooks() as $item)
            <flux:text class="mt-2">
                <a
                    class="hover:underline" 
                    href="{{ route('books.show', ['bookUuid' => $item['uuid']]) }}"
                >🗒️ {{ $item['title'] }}</a>
            </flux:text>
        @endforeach
    </div>
</div>