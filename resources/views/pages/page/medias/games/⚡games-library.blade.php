<?php

use Livewire\Component;

new class extends Component
{
    use \Livewire\WithPagination;
    use \App\Traits\SortTitle;
    use \App\Traits\QueryStrings;

    public $name_collection;
    public $name_subject;
    public $name_category;
    public $name_platform;
    public $name_star;

    //////////////////////////////////////////////////////////////////// PROPIEDADES
    // propiedades de item y titulos
    public $games = [];
    public $titlePage = 'Estanteria';
    public $subtitlePage = 'Estanteria de juegos jugados';
    public $collection_selected, $subject_selected, $category_selected, $platform_selected, $star_selected;

    //////////////////////////////////////////////////////////////////// PRE CARGAR DATOS
    public function mount(){
        $games = $this->gameQuery()->get();
        $this->games = collect($games)
            ->groupBy(function ($game) {
                return $game->playeds_max_end_played
                    ? \Carbon\Carbon::parse($game->playeds_max_end_played)->format('Y')
                    : 'Sin fecha';
            })
            ->sortKeysDesc();

        $this->name_collection = $this->collection_selected ? \App\Models\Page\Collection::where('uuid', $this->collection_selected)->first()->name : null;
        $this->name_subject = $this->subject_selected ? \App\Models\Page\Subject::where('uuid', $this->subject_selected)->first()->name : null;
        $this->name_category = $this->category_selected ? \App\Models\Page\Category::where('uuid', $this->category_selected)->first()->name : null;
        $this->name_platform = $this->platform_selected ? \App\Models\Page\Platform::where('uuid', $this->platform_selected)->first()->name : null;
        $this->name_star = $this->star_selected ? str_repeat('★', $this->star_selected) : null;
    }

    //////////////////////////////////////////////////////////////////// CONSULTA DE LISTADO Y ELIMINAR ITEM
    public function gameQuery(){
        return \App\Models\Page\Game::where('user_id', \Illuminate\Support\Facades\Auth::id())
        
            // no abandonado
            ->whereHas('playeds')
            ->withMax('playeds', 'end_played')
            ->whereHas('playeds', fn($q) => $q->where('end_played', '<>' ,''))

            ->when($this->star_selected !== null, function( $query) {
                return $query->where('rating', $this->star_selected);
            })
            ->when($this->subject_selected, function ($query) {
                $query->whereHas('subjects', function ($q) {
                    $q->where('subjects.uuid', $this->subject_selected);
                });
            })
            ->when($this->category_selected, function ($query) {
                $query->whereHas('categories', function ($q) {
                    $q->where('categories.uuid', $this->category_selected);
                });
            })
            ->when($this->collection_selected, function ($query) {
                $query->whereHas('collections', function ($q) {
                    $q->where('collections.uuid', $this->collection_selected);
                });
            })
            ->when($this->platform_selected, function ($query) {
                $query->whereHas('platforms', function ($q) {
                    $q->where('platforms.uuid', $this->platform_selected);
                });
            })

            ->orderBy('playeds_max_end_played', $this->sortDirection);
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

    {{-- titulos de filtros --}}
    <div>
        <p class="text-start text-lg sm:text-2xl font-bold mb-3">
            @if ($category_selected || $subject_selected || $collection_selected || $platform_selected || $star_selected)
                {{ $this->name_category ? 'Categoria: '.$this->name_category : null }}
                {{ $this->name_subject ? 'Actor: '.$this->name_subject : null }}
                {{ $this->name_collection ? 'Coleccion: '.$this->name_collection : null }}
                {{ $this->name_platform ? 'Plataforma: '.$this->name_platform : null }}
                {{ $this->name_star ? 'Estrellas: '.$this->name_star : null }}
            @endif
        </p>
    </div>

    {{-- cuadricula --}}
    <div class="relative shadow-md sm:rounded-lg">
                @foreach ($games as $year => $games_by_year)
        
            <p class="text-center text-lg sm:text-2xl font-bold mb-3">{{ $year }}</p>
            
            <div class="flex flex-wrap justify-center gap-1 px-1 py-3">
                @foreach ($games_by_year as $item)
                    <a 
                        href="{{ route('games.show', ['gameUuid' => $item->uuid]) }}"
                    >
                        <div class="relative w-16 h-24 sm:w-30 sm:h-44 rounded-lg overflow-hidden shadow-lg group">
                            <img src="{{ $item->cover_image_url ? $item->cover_image_url : asset('images/placeholderVisual.jpg') }}" alt="Portada del pelicula" class="w-full h-full object-cover">
                            <div class="absolute inset-0 bg-black bg-opacity-50 opacity-0 group-hover:opacity-100 flex items-center justify-center transition-opacity duration-300">
                                <div class="text-center space-y-10">
                                    <p class="text-white text-lg font-semibold px-2 text-center">{{ $item->title }}</p>
                                    <p class="relative text-xs italic text-gray-700 dark:text-gray-300">
                                        {{ $item->is_favorite ? '❤️' : ''}}
                                        {{ $item->is_abandonated ? '🚫' : ''}}
                                        {{ $item->summary_clear ? '🗒️' : ''}}
                                        {{ $item->notes_clear ? '✍️' : ''}}
                                        {{ $item->playeds->whereNotNull('end_played')->first() ? '✅' : '' }}
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