<?php

use Livewire\Component;

new class extends Component
{
    //////////////////////////////////////////////////////////////////// PROPIEDADES PRINCIPALES
    // propiedades de item y titulos
    public $books;
    public $title = 'Datos pendientes';
    public $subtitle = 'Datos de libros incompletos';

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
    <div>
        <div container class="mb-1 space-y-1">
            <flux:heading size="xl" level="1">
                <a href="{{ route('books.index') }}"><flux:button size="xs" variant="ghost" icon="arrow-uturn-left"></flux:button></a>
                {{ $this->title }}
            </flux:heading>
            <flux:text class="text-base">{{ $this->subtitle }}</flux:text>
    
            <flux:breadcrumbs>
                <flux:breadcrumbs.item href="{{ route('dashboard') }}">Dashboard</flux:breadcrumbs.item>
                <flux:breadcrumbs.item href="{{ route('books.index') }}">Libros</flux:breadcrumbs.item>
                <flux:breadcrumbs.item>{{ $this->title }}</flux:breadcrumbs.item>
            </flux:breadcrumbs>
    
            <flux:separator variant="subtle" />
            <flux:badge color="purple"><a href="{{ route('books.index') }}">Libros</a></flux:badge>
            <flux:badge color="violet"><a href="{{ route('books_data.index') }}">Estadisticas</a></flux:badge>
        </div>
    </div>

    {{-- pentientes totales de comentar de cualquier año --}}
    <flux:separator text="✍️ Pendientes totales de comentar" />
    <p>Listado con pendientes de comentar ({{ $this->toComment()->count() }})</p>
    @foreach ($this->toComment() as $item)
        <flux:text class="mt-2">
            <a
                class="hover:underline" 
                href="{{ route('books.show', ['bookUuid' => $item['uuid']]) }}"
            >🗒️ {{ $item['title'] }}</a>
        </flux:text>
    @endforeach

    {{-- abandonados totales de cualquier año --}}
    <flux:separator text="🚫 Libros abandonados" />
    <p>Listado de abandonados ({{ $this->abandonatedBooks()->count() }})</p>
    @foreach ($this->abandonatedBooks() as $item)
        <flux:text class="mt-2">
            <a
                class="hover:underline" 
                href="{{ route('books.show', ['bookUuid' => $item['uuid']]) }}"
            >🗒️ {{ $item['title'] }}</a>
        </flux:text>
    @endforeach

    {{-- pendientes totales a leer de cualquier año --}}
    <flux:separator text="📖 Pendientes totales a leer" />
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