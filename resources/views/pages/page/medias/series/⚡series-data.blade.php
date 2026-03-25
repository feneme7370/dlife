<?php

use Livewire\Component;

new class extends Component
{
    //////////////////////////////////////////////////////////////////// PROPIEDADES PRINCIPALES
    // propiedades de item y titulos
    public $series;
    public $titlePage = 'Datos de series';
    public $subtitlePage = 'Estadisticas de series leidos';

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

        // Traés todos los series del usuario (sin filtros por fecha)
        $this->series = \App\Models\Page\Serie::where('user_id', \Illuminate\Support\Facades\Auth::id())
        ->orderBy('title', 'asc')
            ->with(['subjects', 'genres', 'collections', 'views', 'tags'])
            ->get();
    }

    //////////////////////////////////////////////////////////////////// CONSULTA POR AÑO
    // series dentro del rango de año y no abandonados
    public function seriesYear(){
        $year_start = $this->year_start;
        $year_end = $this->year_end;
        return $this->series->where('is_abandonated', false)
            ->filter(function ($serie) use ($year_start, $year_end) {

                return $serie->views->contains(function ($view) use ($year_start, $year_end) {
                    $year = (int) substr($view->end_view, 0, 4); // más rápido que Carbon

                    return
                        (!$year_start || $year >= $year_start) &&
                        (!$year_end   || $year <= $year_end);
                });

            });
    }

    //////////////////////////////////////////////////////////////////// AGRUPAR CONSULTA POR DISTINTOS DATOS
    // agrupar por cantidad de paginas leidas
    // public function seriesPages(){
    //     $order = ['📄 60 mins','📄 90 mins','📄 120 mins','📄 180 mins','📄 180+ mins'];

    //     return $this->seriesYear()
    //         ->groupBy(function ($serie) {
    //             return match (true) {
    //                 $serie->runtime <= 60 => '📄 60 mins',
    //                 $serie->runtime <= 90 => '📄 90 mins',
    //                 $serie->runtime <= 120 => '📄 120 mins',
    //                 $serie->runtime <= 180 => '📄 180 mins',
    //                 default => '📄 180+ mins',
    //             };
    //         })
    //         ->sortBy(fn($_, $key) => array_search($key, $order));
    // }

    // agrupar por mes de vista
    public function monthViews(){
        $year_end = $this->year_end;
        $months = collect(range(1, 12))->mapWithKeys(fn ($m) => [str_pad($m, 2, '0', STR_PAD_LEFT) => 0]);
        return $this->seriesYear()
            ->flatMap(function ($serie) use ($year_end) {
                return $serie->views
                    ->filter(fn ($view) => $view->end_view && \Carbon\Carbon::parse($view->end_view)->year == $year_end)
                    ->map(fn ($view) => \Carbon\Carbon::parse($view->end_view)->format('m'));
            })
            ->countBy()
            ->union($months) // completa los que faltan
            ->sortKeys();    // ordena de 01 a 12;
    }

    //////////////////////////////////////////////////////////////////// OBTENER ASOCIACIONES
    // listado de sagas de series leidos en un año
    public function seriesCollections(){
        return $this->seriesYear()
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

    // listado de generos de series leidos en un año
    public function seriesGenres(){
        return $this->seriesYear()
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

    // listado de autores de series leidos en un año
    public function seriesSubjects(){
        return $this->seriesYear()
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
        return $this->view_years = $this->series
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
    <x-page.partials.title-page 
        :title="$this->titlePage"
        :create-route="'series.index'"
        icon="arrow-uturn-left"
        :breadcrumbs="[
            ['label' => 'Dashboard', 'route' => 'dashboard'],
            ['label' => 'Series', 'route' => 'series.index'],
            ['label' => $this->titlePage]
        ]"
    />

     {{-- toast de mensaje --}}
     <x-libraries.flux.toast-success />
     
    <flux:badge color="violet"><a href="{{ route('series.index') }}">Series</a></flux:badge>

    {{-- filtro por año --}}
    <div class="flex justify-between items-center gap-1">
        <span>{{ $this->seriesYear()->count() }} series</span>
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

    {{-- estadisticas basicas de series leidos en el año y no abandonados --}}
    <flux:separator text="📊 Estadísticas básicas" />
    <div>
        <flux:heading>Totales ({{ $this->seriesYear()->count() }})</flux:heading>
        <div class="grid grid-cols-2 gap-1 my-2">
            <flux:text class="mt-2"><a>📺 {{ $this->seriesYear()->count() }} series</a></flux:text>
            <flux:text class="mt-2"><a>📃 {{ $this->seriesYear()->sum('episodes') }} episodios.</a></flux:text>
            <flux:text class="mt-2"><a>📇 {{ number_format($this->seriesYear()->sum('episodes') / ($this->seriesYear()->count() == 0 ? 1 : $this->seriesYear()->count()), 0) }} ep. prom. series</a></flux:text>
            <flux:text class="mt-2"><a>📅 {{ number_format($this->seriesYear()->sum('episodes') / 12, 0) }} ep. prom. mes</a></flux:text>
        </div>
    </div>

    {{-- valoracion y agrupacion por paginas de series leidos en el año y no abandonados --}}
    <flux:separator text="⭐ Valoraciones y datos" />
    <div class="grid grid-cols-2 gap-3 my-2">
        <div>
            <flux:heading>Calificacion ({{ $this->seriesYear()->count() }})</flux:heading>
            <div>
                @foreach ((collect($this->seriesYear())->groupBy('rating')->map->count()->sortKeysDesc()) as $stars => $count)
                    <flux:text class="mt-2">
                        <a
                            class="hover:underline"
                            href="{{ route('series_library.index', ['star' => $stars]) }}"  
                        >{{ str_repeat('⭐', $stars) }} ({{ $count }})</a>
                    </flux:text>
                @endforeach
            </div>
        </div>
        <div>
            <flux:heading>Episodios ({{ $this->seriesYear()->sum('episodes') }})</flux:heading>
            <div>
                <flux:text class="mt-2"><a>📃 {{ $this->seriesYear()->sum('seasons') }} temps.</a></flux:text>
                <flux:text class="mt-2"><a>📃 {{ $this->seriesYear()->sum('episodes') }} eps.</a></flux:text>
            </div>
            {{-- <div>
                @foreach($this->seriesPages() as $range => $series)
                    <flux:text class="mt-2">
                        <a
                            class="hover:underline"
                            href="{{ route('series_library.index', ['runtime' => $range]) }}"
                        >
                            {{ $range }} ({{ $series->count() }})
                        </a>
                    </flux:text>
                @endforeach
            </div> --}}
        </div>

    </div>

    {{-- clasificacion de generos y sagas de series leidos en el año y no abandonados --}}
    <flux:separator text="🏷 Clasificación de vistas" />
    <div class="grid grid-cols-2 gap-3 my-2">
        <div class="max-h-72 overflow-scroll">
            <flux:heading>Generos ({{ $this->seriesGenres()->count() }})</flux:heading>
            <div>
                @foreach($this->seriesGenres() as $item)
                    <flux:text class="mt-2">
                        <a
                            class="hover:underline"
                            href="{{ route('series_library.index', ['g' => $item['uuid']]) }}"
                        >
                            {{ $item['name'] }} ({{ $item['count'] }})
                        </a>
                    </flux:text>
                @endforeach
            </div>
        </div>
        <div>
            <flux:heading>Sagas ({{ $this->seriesCollections()->count() }})</flux:heading>
            <div class="max-h-72 overflow-scroll">
                @foreach($this->seriesCollections() as $item)
                    <flux:text class="mt-2">
                        <a
                            class="hover:underline"
                            href="{{ route('series_library.index', ['c' => $item['uuid']]) }}"
                        >
                            {{ $item['name'] }} ({{ $item['count'] }})
                        </a>
                    </flux:text>
                @endforeach
            </div>
        </div>

    </div>

    {{-- clasificacion de autores y series leidos en el año y no abandonados --}}
    <flux:separator text="📺 Listado" />
    <div class="grid grid-cols-2 gap-3 my-2">
        <div>
            <flux:heading>Series ({{ $this->seriesYear()->count() }})</flux:heading>
            <div class="max-h-72 overflow-scroll">
                @foreach($this->seriesYear() as $item)
                    <flux:text class="mt-2">
                        <a
                            class="hover:underline"
                            href="{{ route('series.show', ['serieUuid' => $item['uuid']]) }}"
                        >
                            {{ $item['title'] }}
                        </a>
                    </flux:text>
                @endforeach
            </div>
        </div>
        <div>
            <flux:heading>Actores ({{ $this->seriesSubjects()->count() }})</flux:heading>
            <div class="max-h-72 overflow-scroll">
                @foreach($this->seriesSubjects() as $item)
                    <flux:text class="mt-2">
                        <a
                            class="hover:underline"
                            href="{{ route('series_library.index', ['a' => $item['uuid']]) }}"
                        >
                            {{ $item['name'] }} ({{ $item['count'] }})
                        </a>
                    </flux:text>
                @endforeach
            </div>
        </div>

    </div>
    
    {{-- agrupamiento de vista por mes de series leidos en el año y no abandonados --}}
    <flux:separator text="📊 Grafico por mes" />
    @foreach($this->monthViews() as $month => $total)
        <flux:text class="mt-2">
            {{ $this->diccionario[$month] }}:
            {{ str_repeat('📺', $total) }}
            @if ($total)
                <span class="text-xs italic">({{ $total }})</span>
            @endif
        </flux:text>
    @endforeach
</div>