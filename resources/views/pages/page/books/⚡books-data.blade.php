<?php

use Livewire\Component;

new class extends Component
{
    //////////////////////////////////////////////////////////////////// PROPIEDADES PRINCIPALES
    // propiedades de item y titulos
    public $books;
    public $titlePage = 'Datos de libros';
    public $subtitlePage = 'Estadisticas de libros leidos';

    // fecjas de inicio y fin
    public $year_start, $year_end;

    // filtro con años leidos
    public $read_years;

    // diccionario de meses
    public $diccionario = [
        '01' => 'Ene',
        '02' => 'Feb',
        '03' => 'Mar',
        '04' => 'Abr',
        '05' => 'May',
        '06' => 'Jun',
        '07' => 'Jul',
        '08' => 'Ago',
        '09' => 'Sep',
        '10' => 'Oct',
        '11' => 'Nov',
        '12' => 'Dic',
    ];

    //////////////////////////////////////////////////////////////////// PRECARGAR DATOS INICIALES
    // cargar datos iniciales
    public function mount(){
        // year today
        $this->year_start = \Carbon\Carbon::now()->format('Y');
        $this->year_end = \Carbon\Carbon::now()->format('Y');

        // Traés todos los libros del usuario (sin filtros por fecha)
        $this->books = \App\Models\Page\Book::where('user_id', \Illuminate\Support\Facades\Auth::id())
        ->orderBy('title', 'asc')
            ->with(['subjects', 'genres', 'collections', 'reads', 'tags'])
            ->get();
    }

    //////////////////////////////////////////////////////////////////// CONSULTA POR AÑO
    // libros dentro del rango de año y no abandonados
    public function booksYear(){
        $year_start = $this->year_start;
        $year_end = $this->year_end;
        return $this->books->where('is_abandonated', false)
            ->filter(function ($book) use ($year_start, $year_end) {

                return $book->reads->contains(function ($read) use ($year_start, $year_end) {
                    $year = (int) substr($read->end_read, 0, 4); // más rápido que Carbon

                    return
                        (!$year_start || $year >= $year_start) &&
                        (!$year_end   || $year <= $year_end);
                });

            });
    }

    //////////////////////////////////////////////////////////////////// AGRUPAR CONSULTA POR DISTINTOS DATOS
    // agrupar por cantidad de paginas leidas
    public function booksPages(){
        $order = ['📄 0-250','📄 251-500','📄 501-750','📄 751-1000','📄 1000+'];

        return $this->booksYear()
            ->groupBy(function ($book) {
                return match (true) {
                    $book->pages <= 250 => '📄 0-250',
                    $book->pages <= 500 => '📄 251-500',
                    $book->pages <= 750 => '📄 501-750',
                    $book->pages <= 1000 => '📄 751-1000',
                    default => '📄 1000+',
                };
            })
            ->sortBy(fn($_, $key) => array_search($key, $order));
    }

    // agrupar por mes de lectura
    public function monthReads(){
        $year_end = $this->year_end;
        $months = collect(range(1, 12))->mapWithKeys(fn ($m) => [str_pad($m, 2, '0', STR_PAD_LEFT) => 0]);
        return $this->booksYear()
            ->flatMap(function ($book) use ($year_end) {
                return $book->reads
                    ->filter(fn ($read) => $read->end_read && \Carbon\Carbon::parse($read->end_read)->year == $year_end)
                    ->map(fn ($read) => \Carbon\Carbon::parse($read->end_read)->format('m'));
            })
            ->countBy()
            ->union($months) // completa los que faltan
            ->sortKeys();    // ordena de 01 a 12;
    }

    //////////////////////////////////////////////////////////////////// OBTENER ASOCIACIONES
    // listado de sagas de libros leidos en un año
    public function booksCollections(){
        return $this->booksYear()
            ->flatMap->collections
            ->groupBy('id')
            ->map(function ($group) {
                $collection = $group->first();

                return [
                    'name' => $collection->name,
                    'uuid' => $collection->uuid,
                    'count' => $group->count(),
                ];
            })
            ->sortByDesc('count')
            ->values();
    }    

    // listado de generos de libros leidos en un año
    public function booksGenres(){
        return $this->booksYear()
            ->flatMap->genres
            ->groupBy('id')
            ->map(function ($group) {
                $genre = $group->first();

                return [
                    'name' => $genre->name,
                    'uuid' => $genre->uuid,
                    'count' => $group->count(),
                ];
            })
            ->sortByDesc('count')
            ->values();
    }  

    // listado de autores de libros leidos en un año
    public function booksSubjects(){
        return $this->booksYear()
            ->flatMap->subjects
            ->groupBy('id')
            ->map(function ($group) {
                $subject = $group->first();

                return [
                    'name' => $subject->name,
                    'uuid' => $subject->uuid,
                    'count' => $group->count(),
                ];
            })
            ->sortByDesc('count')
            ->values();
    }    

    //////////////////////////////////////////////////////////////////// FILTRO DE AÑO
    // chage new year filter
    public function newYear($value){
        if($value === 'todo'){
            $this->year_start = 1900;
            $this->year_end = 2300;
        }else{
            $this->year_start = $value;
            $this->year_end = $value;
        }
    }

    // Sacar los años únicos para el filtro
    public function getYearsReads(){
        return $this->read_years = $this->books
            ->pluck('reads')          // me quedo solo con las colecciones de reads
            ->flatten()                    // aplanar todo en una sola colección
            ->pluck('end_read')            // me quedo con las fechas end_read
            ->filter()                     // saco nulos
            ->map(fn($date) => \Carbon\Carbon::parse($date)->year) // paso a año
            ->unique()                     // elimino duplicados
            ->sortDesc()                   // ordeno los años
            ->values();                    // limpio los índices
    }
};
?>

<div>
    {{-- titulo, descripcion y breadcrumbs --}}
    <div>
        <div container class="mb-1 space-y-1">
            <flux:heading size="xl" level="1">
                <a href="{{ route('books.index') }}"><flux:button size="xs" variant="ghost" icon="arrow-uturn-left"></flux:button></a>
                {{ $this->titlePage }}
            </flux:heading>
            <flux:text class="text-base">{{ $this->subtitlePage }}</flux:text>
    
            <flux:breadcrumbs>
                <flux:breadcrumbs.item href="{{ route('dashboard') }}">Dashboard</flux:breadcrumbs.item>
                <flux:breadcrumbs.item href="{{ route('books.index') }}">Libros</flux:breadcrumbs.item>
                <flux:breadcrumbs.item>{{ $this->titlePage }}</flux:breadcrumbs.item>
            </flux:breadcrumbs>
    
            <flux:separator variant="subtle" />

            {{-- links para pendientes y estadisticas --}}
            <div class="mt-1">
                <flux:badge color="violet"><a href="{{ route('books.index') }}">Libros</a></flux:badge>
                <flux:badge color="purple"><a href="{{ route('books_incomplete.index') }}">Pendientes</a></flux:badge>
            </div>
        </div>
    </div>

    {{-- filtro por año --}}
    <div class="flex justify-between items-center gap-1">
        <span>{{ $this->booksYear()->count() }} libros</span>
        <flux:dropdown>
            <flux:button class="col-span-1 text-center" icon:trailing="chevron-down">{{ $this->year_start == 1900 ? 'Todos' : $this->year_start }}</flux:button>

            <flux:menu>
                <flux:menu.radio.group>
                    <flux:menu.radio wire:navigated wire:click="newYear('todo')">Todos</flux:menu.radio>
                    @foreach ($this->getYearsReads() as $read_year)
                        <flux:menu.radio wire:click="newYear({{ $read_year }})">{{ $read_year }}</flux:menu.radio>
                    @endforeach
                </flux:menu.radio.group>
            </flux:menu>
        </flux:dropdown>
    </div>

    {{-- estadisticas basicas de libros leidos en el año y no abandonados --}}
    <flux:separator text="📊 Estadísticas básicas" />
    <div class="grid grid-cols-1 md:grid-cols-2 gap-3 my-2">
        <div>
            <flux:heading>Totales ({{ $this->booksYear()->count() }})</flux:heading>
            <flux:text class="mt-2"><a>📙 {{ $this->booksYear()->count() }} libros</a></flux:text>
            <flux:text class="mt-2"><a>📃 {{ $this->booksYear()->sum('pages') }} pags.</a></flux:text>
            <flux:text class="mt-2"><a>📇 {{ number_format($this->booksYear()->sum('pages') / ($this->booksYear()->count() == 0 ? 1 : $this->booksYear()->count()), 0) }} prom. libros</a></flux:text>
            <flux:text class="mt-2"><a>📅 {{ number_format($this->booksYear()->sum('pages') / 12, 0) }} prom. mes</a></flux:text>
        </div>
    </div>

    {{-- valoracion y agrupacion por paginas de libros leidos en el año y no abandonados --}}
    <flux:separator text="⭐ Valoraciones y datos" />
    <div class="grid grid-cols-1 md:grid-cols-2 gap-3 my-2">
        <div>
            <flux:heading>Calificacion ({{ $this->booksYear()->count() }})</flux:heading>
            @foreach ((collect($this->booksYear())->groupBy('rating')->map->count()->sortKeysDesc()) as $stars => $count)
                <flux:text class="mt-2">
                    <a
                        class="hover:underline"
                        href="{{ route('books_library.index', ['star' => $stars]) }}"  
                    >{{ str_repeat('⭐', $stars) }} ({{ $count }})</a>
                </flux:text>
            @endforeach
        </div>
        <div>
            <flux:heading>Paginas ({{ $this->booksYear()->count() }})</flux:heading>
            <div>
                @foreach($this->booksPages() as $range => $books)
                    <flux:text class="mt-2">
                        <a
                            class="hover:underline"
                            href="{{ route('books_library.index', ['pages' => $range]) }}"
                        >
                            {{ $range }} ({{ $books->count() }})
                        </a>
                    </flux:text>
                @endforeach
            </div>
        </div>

    </div>

    {{-- clasificacion de generos y sagas de libros leidos en el año y no abandonados --}}
    <flux:separator text="🏷 Clasificación de lecturas" />
    <div class="grid grid-cols-1 md:grid-cols-2 gap-3 my-2">
        <div>
            <flux:heading>Generos ({{ $this->booksGenres()->count() }})</flux:heading>
            <div>
                @foreach($this->booksGenres() as $item)
                    <flux:text class="mt-2">
                        <a
                            class="hover:underline"
                            href="{{ route('books_library.index', ['g' => $item['uuid']]) }}"
                        >
                            {{ $item['name'] }} ({{ $item['count'] }})
                        </a>
                    </flux:text>
                @endforeach
            </div>
        </div>
        <div>
            <flux:heading>Sagas ({{ $this->booksCollections()->count() }})</flux:heading>
            <div>
                @foreach($this->booksCollections() as $item)
                    <flux:text class="mt-2">
                        <a
                            class="hover:underline"
                            href="{{ route('books_library.index', ['c' => $item['uuid']]) }}"
                        >
                            {{ $item['name'] }} ({{ $item['count'] }})
                        </a>
                    </flux:text>
                @endforeach
            </div>
        </div>

    </div>

    {{-- clasificacion de autores y libros leidos en el año y no abandonados --}}
    <flux:separator text="📖 Listado" />
    <div class="grid grid-cols-1 md:grid-cols-2 gap-3 my-2">
        <div>
            <flux:heading>Libros ({{ $this->booksYear()->count() }})</flux:heading>
            <div>
                @foreach($this->booksYear() as $item)
                    <flux:text class="mt-2">
                        <a
                            class="hover:underline"
                            href="{{ route('books.show', ['bookUuid' => $item['uuid']]) }}"
                        >
                            {{ $item['title'] }}
                        </a>
                    </flux:text>
                @endforeach
            </div>
        </div>
        <div>
            <flux:heading>Autores ({{ $this->booksSubjects()->count() }})</flux:heading>
            <div>
                @foreach($this->booksSubjects() as $item)
                    <flux:text class="mt-2">
                        <a
                            class="hover:underline"
                            href="{{ route('books_library.index', ['a' => $item['uuid']]) }}"
                        >
                            {{ $item['name'] }} ({{ $item['count'] }})
                        </a>
                    </flux:text>
                @endforeach
            </div>
        </div>

    </div>
    
    {{-- agrupamiento de lectura por mes de libros leidos en el año y no abandonados --}}
    <flux:separator text="📊 Grafico por mes" />
    @foreach($this->monthReads() as $month => $total)
        <flux:text class="mt-2">
            {{ $this->diccionario[$month] }}:
            {{ str_repeat('📖', $total) }}
            @if ($total)
                <span class="text-xs italic">({{ $total }})</span>
            @endif
        </flux:text>
    @endforeach
</div>