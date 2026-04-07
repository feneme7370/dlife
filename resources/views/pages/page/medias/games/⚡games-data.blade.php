<?php

use Livewire\Component;

new class extends Component
{
    //////////////////////////////////////////////////////////////////// PROPIEDADES PRINCIPALES
    // propiedades de item y titulos
    public $games;
    public $titlePage = 'Datos de juegos';
    public $subtitlePage = 'Estadisticas de juegos leidos';

    // fecjas de inicio y fin
    public $year_start, $year_end;

    // filtro con años leidos
    public $played_years;

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

        // Traés todos los juegos del usuario (sin filtros por fecha)
        $this->games = \App\Models\Page\Game::where('user_id', \Illuminate\Support\Facades\Auth::id())
        ->orderBy('title', 'asc')
            ->with(['subjects', 'categories', 'collections', 'platforms', 'playeds', 'tags'])
            ->get();
    }

    //////////////////////////////////////////////////////////////////// CONSULTA POR AÑO
    // juegos dentro del rango de año y no abandonados
    public function gamesYear(){
        $year_start = $this->year_start;
        $year_end = $this->year_end;
        return $this->games->where('is_abandonated', false)
            ->filter(function ($game) use ($year_start, $year_end) {

                return $game->playeds->contains(function ($played) use ($year_start, $year_end) {
                    $year = (int) substr($played->end_played, 0, 4); // más rápido que Carbon

                    return
                        (!$year_start || $year >= $year_start) &&
                        (!$year_end   || $year <= $year_end);
                });

            });
    }

    //////////////////////////////////////////////////////////////////// AGRUPAR CONSULTA POR DISTINTOS DATOS
    // agrupar por mes de vista
    public function monthPlayeds(){
        $year_end = $this->year_end;
        $months = collect(range(1, 12))->mapWithKeys(fn ($m) => [str_pad($m, 2, '0', STR_PAD_LEFT) => 0]);
        return $this->gamesYear()
            ->flatMap(function ($game) use ($year_end) {
                return $game->playeds
                    ->filter(fn ($played) => $played->end_played && \Carbon\Carbon::parse($played->end_played)->year == $year_end)
                    ->map(fn ($played) => \Carbon\Carbon::parse($played->end_played)->format('m'));
            })
            ->countBy()
            ->union($months) // completa los que faltan
            ->sortKeys();    // ordena de 01 a 12;
    }

    //////////////////////////////////////////////////////////////////// OBTENER ASOCIACIONES
    // listado de sagas de juegos leidos en un año
    public function gamesCollections(){
        return $this->gamesYear()
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

    // listado de generos de juegos leidos en un año
    public function gamesCategories(){
        return $this->gamesYear()
            ->flatMap->categories
            ->groupBy('id')
            ->map(function ($group) {
                $category = $group->first();

                return [
                    'name' => $category->name,
                    'uuid' => $category->uuid,
                    'count' => $group->count(),
                ];
            })
            ->sortByDesc('count')
            ->values();
    }  

    // listado de autores de juegos leidos en un año
    public function gamesSubjects(){
        return $this->gamesYear()
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

    // listado de autores de juegos leidos en un año
    public function gamesPlatforms(){
        return $this->gamesYear()
            ->flatMap->platforms
            ->groupBy('id')
            ->map(function ($group) {
                $platform = $group->first();

                return [
                    'name' => $platform->name,
                    'uuid' => $platform->uuid,
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
    public function getYearsPlayeds(){
        return $this->played_years = $this->games
            ->pluck('playeds')          // me quedo solo con las colecciones de playeds
            ->flatten()                    // aplanar todo en una sola colección
            ->pluck('end_played')            // me quedo con las fechas end_played
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
        :create-route="'games.index'"
        icon="arrow-uturn-left"
        :breadcrumbs="[
            ['label' => 'Dashboard', 'route' => 'dashboard'],
            ['label' => 'Juegos', 'route' => 'games.index'],
            ['label' => $this->titlePage]
        ]"
    />

     {{-- toast de mensaje --}}
     <x-libraries.flux.toast-success />

    {{-- filtro por año --}}
    <div class="flex justify-between items-center gap-1">
        <span>{{ $this->gamesYear()->count() }} juegos</span>
        <flux:dropdown>
            <flux:button class="col-span-1 text-center" icon:trailing="chevron-down">{{ $this->year_start == 1900 ? 'Todos' : $this->year_start }}</flux:button>

            <flux:menu>
                <flux:menu.radio.group>
                    <flux:menu.radio wire:navigated wire:click="newYear('todo')">Todos</flux:menu.radio>
                    @foreach ($this->getYearsPlayeds() as $played_year)
                        <flux:menu.radio wire:click="newYear({{ $played_year }})">{{ $played_year }}</flux:menu.radio>
                    @endforeach
                </flux:menu.radio.group>
            </flux:menu>
        </flux:dropdown>
    </div>

    {{-- estadisticas basicas de juegos leidos en el año y no abandonados --}}
    <flux:separator text="📊 Estadísticas básicas" />
    <div>
        <flux:heading>Totales ({{ $this->gamesYear()->count() }})</flux:heading>
        <div class="grid grid-cols-2 gap-1 my-2">
            <flux:text class="mt-2"><a>🕹️ {{ $this->gamesYear()->count() }} juegos</a></flux:text>
        </div>
    </div>

    {{-- valoracion y agrupacion por paginas de juegos leidos en el año y no abandonados --}}
    <flux:separator text="⭐ Valoraciones y datos" />
    <div class="grid grid-cols-2 gap-3 my-2">
        <div>
            <flux:heading>Calificacion ({{ $this->gamesYear()->count() }})</flux:heading>
            <div>
                @foreach ((collect($this->gamesYear())->groupBy('rating')->map->count()->sortKeysDesc()) as $stars => $count)
                    <flux:text class="mt-2">
                        <a
                            class="hover:underline"
                            href="{{ route('games_library.index', ['star' => $stars]) }}"  
                        >{{ str_repeat('⭐', $stars) }} ({{ $count }})</a>
                    </flux:text>
                @endforeach
            </div>
        </div>
        <div class="max-h-72 overflow-scroll">
            <flux:heading>Plataformas ({{ $this->gamesPlatforms()->count() }})</flux:heading>
            <div>
                @foreach($this->gamesPlatforms() as $item)
                    <flux:text class="mt-2">
                        <a
                            class="hover:underline"
                            href="{{ route('games_library.index', ['plat' => $item['uuid']]) }}"
                        >
                            {{ $item['name'] }} ({{ $item['count'] }})
                        </a>
                    </flux:text>
                @endforeach
            </div>
        </div>

    </div>

    {{-- clasificacion de generos y sagas de juegos leidos en el año y no abandonados --}}
    <flux:separator text="🏷 Clasificación de vistas" />
    <div class="grid grid-cols-2 gap-3 my-2">
        <div class="max-h-72 overflow-scroll">
            <flux:heading>Categorias ({{ $this->gamesCategories()->count() }})</flux:heading>
            <div>
                @foreach($this->gamesCategories() as $item)
                    <flux:text class="mt-2">
                        <a
                            class="hover:underline"
                            href="{{ route('games_library.index', ['cat' => $item['uuid']]) }}"
                        >
                            {{ $item['name'] }} ({{ $item['count'] }})
                        </a>
                    </flux:text>
                @endforeach
            </div>
        </div>
        <div>
            <flux:heading>Sagas ({{ $this->gamesCollections()->count() }})</flux:heading>
            <div class="max-h-72 overflow-scroll">
                @foreach($this->gamesCollections() as $item)
                    <flux:text class="mt-2">
                        <a
                            class="hover:underline"
                            href="{{ route('games_library.index', ['c' => $item['uuid']]) }}"
                        >
                            {{ $item['name'] }} ({{ $item['count'] }})
                        </a>
                    </flux:text>
                @endforeach
            </div>
        </div>

    </div>

    {{-- clasificacion de autores y juegos leidos en el año y no abandonados --}}
    <flux:separator text="🕹️ Listado" />
    <div class="grid grid-cols-2 gap-3 my-2">
        <div>
            <flux:heading>Juegos ({{ $this->gamesYear()->count() }})</flux:heading>
            <div class="max-h-72 overflow-scroll">
                @foreach($this->gamesYear() as $item)
                    <flux:text class="mt-2">
                        <a
                            class="hover:underline"
                            href="{{ route('games.show', ['gameUuid' => $item['uuid']]) }}"
                        >
                            {{ $item['title'] }}
                        </a>
                    </flux:text>
                @endforeach
            </div>
        </div>
        <div>
            <flux:heading>Desarrolladores ({{ $this->gamesSubjects()->count() }})</flux:heading>
            <div class="max-h-72 overflow-scroll">
                @foreach($this->gamesSubjects() as $item)
                    <flux:text class="mt-2">
                        <a
                            class="hover:underline"
                            href="{{ route('games_library.index', ['a' => $item['uuid']]) }}"
                        >
                            {{ $item['name'] }} ({{ $item['count'] }})
                        </a>
                    </flux:text>
                @endforeach
            </div>
        </div>

    </div>
    
    {{-- agrupamiento de vista por mes de juegos leidos en el año y no abandonados --}}
    <flux:separator text="📊 Grafico por mes" />
    @foreach($this->monthPlayeds() as $month => $total)
        <flux:text class="mt-2">
            {{ $this->diccionario[$month] }}:
            {{ str_repeat('🕹️', $total) }}
            @if ($total)
                <span class="text-xs italic">({{ $total }})</span>
            @endif
        </flux:text>
    @endforeach
</div>