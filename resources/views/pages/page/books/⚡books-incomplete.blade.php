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
        return $this->books->filter(fn($book) => !$book->notes_clear != '' || !$book->notes_clear != null);
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

    {{-- pentientes totales de comentar de cualquier año --}}
    <flux:separator text="✍️ Pendientes totales de comentar" />
    <div>
        <p>Listado con pendientes de comentar ({{ $this->toComment()->count() }})</p>
        @foreach ($this->toComment() as $item)
            <flux:text class="mt-2">
                <a
                    class="hover:underline" 
                    href="{{ route('books.show', ['bookUuid' => $item['uuid']]) }}"
                >🗒️ {{ $item['title'] }}</a>
            </flux:text>
        @endforeach
    </div>

    {{-- abandonados totales de cualquier año --}}
    <flux:separator text="🚫 Libros abandonados" />
    <div>
        <p>Listado de abandonados ({{ $this->abandonatedBooks()->count() }})</p>
        @foreach ($this->abandonatedBooks() as $item)
            <flux:text class="mt-2">
                <a
                    class="hover:underline" 
                    href="{{ route('books.show', ['bookUuid' => $item['uuid']]) }}"
                >🗒️ {{ $item['title'] }}</a>
            </flux:text>
        @endforeach
    </div>

    {{-- pendientes totales a leer de cualquier año --}}
    <flux:separator text="📖 Pendientes totales a leer" />
    <div>
        <p>Listado con pendientes de leer ({{ $this->toRead()->count() }})</p>
        @foreach ($this->toRead() as $item)
            <flux:text class="mt-2">
                <a
                    class="hover:underline" 
                    href="{{ route('books.show', ['bookUuid' => $item['uuid']]) }}"
                >🗒️ {{ $item['title'] }}</a>
            </flux:text>
        @endforeach
    </div>
</div>