<?php

use Livewire\Component;

new class extends Component
{
    //////////////////////////////////////////////////////////////////// PROPIEDADES PRINCIPALES
    // propiedades de item y titulos
    public $movies;
    public $titlePage = 'Datos de peliculas';
    public $subtitlePage = 'Estadisticas de peliculas leidos';

    // fecjas de inicio y fin
    public $year_start, $year_end;

    // filtro con años leidos
    public $view_years;

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

        // Traés todos los peliculas del usuario (sin filtros por fecha)
        $this->movies = \App\Models\Page\Movie::where('user_id', \Illuminate\Support\Facades\Auth::id())
        ->orderBy('title', 'asc')
            ->with(['subjects', 'genres', 'collections', 'views', 'tags'])
            ->get();
    }

    //////////////////////////////////////////////////////////////////// CONSULTA POR AÑO
    // peliculas dentro del rango de año y no abandonados
    public function moviesYear(){
        $year_start = $this->year_start;
        $year_end = $this->year_end;
        return $this->movies->where('is_abandonated', false)
            ->filter(function ($movie) use ($year_start, $year_end) {

                return $movie->views->contains(function ($view) use ($year_start, $year_end) {
                    $year = (int) substr($view->end_view, 0, 4); // más rápido que Carbon

                    return
                        (!$year_start || $year >= $year_start) &&
                        (!$year_end   || $year <= $year_end);
                });

            });
    }

    //////////////////////////////////////////////////////////////////// AGRUPAR CONSULTA POR DISTINTOS DATOS
    // agrupar por cantidad de paginas leidas
    public function moviesPages(){
        $order = ['📄 60 mins','📄 90 mins','📄 120 mins','📄 180 mins','📄 180+ mins'];

        return $this->moviesYear()
            ->groupBy(function ($movie) {
                return match (true) {
                    $movie->runtime <= 60 => '📄 60 mins',
                    $movie->runtime <= 90 => '📄 90 mins',
                    $movie->runtime <= 120 => '📄 120 mins',
                    $movie->runtime <= 180 => '📄 180 mins',
                    default => '📄 180+ mins',
                };
            })
            ->sortBy(fn($_, $key) => array_search($key, $order));
    }

    // agrupar por mes de vista
    public function monthViews(){
        $year_end = $this->year_end;
        $months = collect(range(1, 12))->mapWithKeys(fn ($m) => [str_pad($m, 2, '0', STR_PAD_LEFT) => 0]);
        return $this->moviesYear()
            ->flatMap(function ($movie) use ($year_end) {
                return $movie->views
                    ->filter(fn ($view) => $view->end_view && \Carbon\Carbon::parse($view->end_view)->year == $year_end)
                    ->map(fn ($view) => \Carbon\Carbon::parse($view->end_view)->format('m'));
            })
            ->countBy()
            ->union($months) // completa los que faltan
            ->sortKeys();    // ordena de 01 a 12;
    }

    //////////////////////////////////////////////////////////////////// OBTENER ASOCIACIONES
    // listado de sagas de peliculas leidos en un año
    public function moviesCollections(){
        return $this->moviesYear()
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

    // listado de generos de peliculas leidos en un año
    public function moviesGenres(){
        return $this->moviesYear()
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

    // listado de autores de peliculas leidos en un año
    public function moviesSubjects(){
        return $this->moviesYear()
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
    public function getYearsViews(){
        return $this->view_years = $this->movies
            ->pluck('views')          // me quedo solo con las colecciones de views
            ->flatten()                    // aplanar todo en una sola colección
            ->pluck('end_view')            // me quedo con las fechas end_view
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
                <a href="{{ route('movies.index') }}"><flux:button size="xs" variant="ghost" icon="arrow-uturn-left"></flux:button></a>
                {{ $this->titlePage }}
            </flux:heading>
            <flux:text class="text-base">{{ $this->subtitlePage }}</flux:text>
    
            <flux:breadcrumbs>
                <flux:breadcrumbs.item href="{{ route('dashboard') }}">Dashboard</flux:breadcrumbs.item>
                <flux:breadcrumbs.item href="{{ route('movies.index') }}">Peliculas</flux:breadcrumbs.item>
                <flux:breadcrumbs.item>{{ $this->titlePage }}</flux:breadcrumbs.item>
            </flux:breadcrumbs>
    
            <flux:separator variant="subtle" />

            {{-- links para pendientes y estadisticas --}}
            <div class="mt-1">
                <flux:badge color="violet"><a href="{{ route('movies.index') }}">Peliculas</a></flux:badge>
                {{-- <flux:badge color="purple"><a href="{{ route('movies_incomplete.index') }}">Pendientes</a></flux:badge> --}}
            </div>
        </div>
    </div>

    {{-- filtro por año --}}
    <div class="flex justify-between items-center gap-1">
        <span>{{ $this->moviesYear()->count() }} peliculas</span>
        <flux:dropdown>
            <flux:button class="col-span-1 text-center" icon:trailing="chevron-down">{{ $this->year_start == 1900 ? 'Todos' : $this->year_start }}</flux:button>

            <flux:menu>
                <flux:menu.radio.group>
                    <flux:menu.radio wire:navigated wire:click="newYear('todo')">Todos</flux:menu.radio>
                    @foreach ($this->getYearsViews() as $view_year)
                        <flux:menu.radio wire:click="newYear({{ $view_year }})">{{ $view_year }}</flux:menu.radio>
                    @endforeach
                </flux:menu.radio.group>
            </flux:menu>
        </flux:dropdown>
    </div>

    {{-- estadisticas basicas de peliculas leidos en el año y no abandonados --}}
    <flux:separator text="📊 Estadísticas básicas" />
    <div>
        <flux:heading>Totales ({{ $this->moviesYear()->count() }})</flux:heading>
        <div class="grid grid-cols-2 gap-1 my-2">
            <flux:text class="mt-2"><a>🎥 {{ $this->moviesYear()->count() }} peliculas</a></flux:text>
            <flux:text class="mt-2"><a>📃 {{ $this->moviesYear()->sum('runtime') }} mins.</a></flux:text>
            <flux:text class="mt-2"><a>📇 {{ number_format($this->moviesYear()->sum('runtime') / ($this->moviesYear()->count() == 0 ? 1 : $this->moviesYear()->count()), 0) }} mins. prom. peliculas</a></flux:text>
            <flux:text class="mt-2"><a>📅 {{ number_format($this->moviesYear()->sum('runtime') / 12, 0) }} mins. prom. mes</a></flux:text>
        </div>
    </div>

    {{-- valoracion y agrupacion por paginas de peliculas leidos en el año y no abandonados --}}
    <flux:separator text="⭐ Valoraciones y datos" />
    <div class="grid grid-cols-2 gap-3 my-2">
        <div>
            <flux:heading>Calificacion ({{ $this->moviesYear()->count() }})</flux:heading>
            <div>
                @foreach ((collect($this->moviesYear())->groupBy('rating')->map->count()->sortKeysDesc()) as $stars => $count)
                    <flux:text class="mt-2">
                        <a
                            class="hover:underline"
                            href="{{ route('movies_library.index', ['star' => $stars]) }}"  
                        >{{ str_repeat('⭐', $stars) }} ({{ $count }})</a>
                    </flux:text>
                @endforeach
            </div>
        </div>
        <div>
            <flux:heading>Paginas ({{ $this->moviesYear()->count() }})</flux:heading>
            <div>
                @foreach($this->moviesPages() as $range => $movies)
                    <flux:text class="mt-2">
                        <a
                            class="hover:underline"
                            href="{{ route('movies_library.index', ['runtime' => $range]) }}"
                        >
                            {{ $range }} ({{ $movies->count() }})
                        </a>
                    </flux:text>
                @endforeach
            </div>
        </div>

    </div>

    {{-- clasificacion de generos y sagas de peliculas leidos en el año y no abandonados --}}
    <flux:separator text="🏷 Clasificación de vistas" />
    <div class="grid grid-cols-2 gap-3 my-2">
        <div class="max-h-72 overflow-scroll">
            <flux:heading>Generos ({{ $this->moviesGenres()->count() }})</flux:heading>
            <div>
                @foreach($this->moviesGenres() as $item)
                    <flux:text class="mt-2">
                        <a
                            class="hover:underline"
                            href="{{ route('movies_library.index', ['g' => $item['uuid']]) }}"
                        >
                            {{ $item['name'] }} ({{ $item['count'] }})
                        </a>
                    </flux:text>
                @endforeach
            </div>
        </div>
        <div>
            <flux:heading>Sagas ({{ $this->moviesCollections()->count() }})</flux:heading>
            <div class="max-h-72 overflow-scroll">
                @foreach($this->moviesCollections() as $item)
                    <flux:text class="mt-2">
                        <a
                            class="hover:underline"
                            href="{{ route('movies_library.index', ['c' => $item['uuid']]) }}"
                        >
                            {{ $item['name'] }} ({{ $item['count'] }})
                        </a>
                    </flux:text>
                @endforeach
            </div>
        </div>

    </div>

    {{-- clasificacion de autores y peliculas leidos en el año y no abandonados --}}
    <flux:separator text="🎥 Listado" />
    <div class="grid grid-cols-2 gap-3 my-2">
        <div>
            <flux:heading>Peliculas ({{ $this->moviesYear()->count() }})</flux:heading>
            <div class="max-h-72 overflow-scroll">
                @foreach($this->moviesYear() as $item)
                    <flux:text class="mt-2">
                        <a
                            class="hover:underline"
                            href="{{ route('movies.show', ['movieUuid' => $item['uuid']]) }}"
                        >
                            {{ $item['title'] }}
                        </a>
                    </flux:text>
                @endforeach
            </div>
        </div>
        <div>
            <flux:heading>Actores ({{ $this->moviesSubjects()->count() }})</flux:heading>
            <div class="max-h-72 overflow-scroll">
                @foreach($this->moviesSubjects() as $item)
                    <flux:text class="mt-2">
                        <a
                            class="hover:underline"
                            href="{{ route('movies_library.index', ['a' => $item['uuid']]) }}"
                        >
                            {{ $item['name'] }} ({{ $item['count'] }})
                        </a>
                    </flux:text>
                @endforeach
            </div>
        </div>

    </div>
    
    {{-- agrupamiento de vista por mes de peliculas leidos en el año y no abandonados --}}
    <flux:separator text="📊 Grafico por mes" />
    @foreach($this->monthViews() as $month => $total)
        <flux:text class="mt-2">
            {{ $this->diccionario[$month] }}:
            {{ str_repeat('🎥', $total) }}
            @if ($total)
                <span class="text-xs italic">({{ $total }})</span>
            @endif
        </flux:text>
    @endforeach
</div>