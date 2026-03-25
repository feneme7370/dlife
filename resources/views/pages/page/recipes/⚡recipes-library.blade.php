<?php

use Livewire\Component;

new class extends Component
{
    use \Livewire\WithPagination;

    //////////////////////////////////////////////////////////////////// PROPIEDADES PARA PAGINACION
    // propiedades para paginacion y orden, actualizar al buscar
    public $search = '', $sortField = 'title', $sortDirection = 'desc', $perPage = 10000;
    public function updatingSearch(){$this->resetPage();}
    public function updatingSortField(){$this->resetPage();}
    public function updatingSortDirection(){$this->resetPage();}
    public function updatingPerPage(){$this->resetPage();}

    // funcion para ordenar la tabla
    public function sortBy($field){
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortDirection = 'asc';
        }
        $this->sortField = $field;
    }

    // mostrar variables en queryString
    protected function queryString(){
        return [
        'search' => [ 'as' => 'q' ],
        'categories_selected' => [ 'as' => 'cat' ],
        'tag_selected' => [ 'as' => 'tag' ],
        ];
    }

    //////////////////////////////////////////////////////////////////// PROPIEDADES PRINCIPALES
    // propiedades de item y titulos
    public $recipes;
    public $titlePage = 'Recetario';
    public $subtitlePage = 'Listado de recetas leidos';
    public $categories_selected, $tags_selected;

    //////////////////////////////////////////////////////////////////// PRE CARGAR DATOS
    public function mount(){
        $this->recipes = $this->movieQuery()->get();
    }

    //////////////////////////////////////////////////////////////////// CONSULTA DE DATOS
    public function recipesQuery(){
        return \App\Models\Page\Recipe::where('user_id', \Illuminate\Support\Facades\Auth::id())

            ->when($this->categories_selected, function ($query) {
                $query->whereHas('categories', function ($q) {
                    $q->where('rcategories.uuid', $this->categories_selected);
                });
            })
            ->when($this->tags_selected, function ($query) {
                $query->whereHas('tags', function ($q) {
                    $q->where('rtags.uuid', $this->tags_selected);
                });
            })

            ->orderBy($this->sortField, $this->sortDirection);
    }
};
?>

<div>
     {{-- titulo, descripcion y breadcrumbs --}}
    <x-page.partials.title-page 
        :title="$this->titlePage"
        :create-route="'recipes.index'"
        icon="arrow-uturn-left"
        :breadcrumbs="[
            ['label' => 'Dashboard', 'route' => 'dashboard'],
            ['label' => 'Recetas', 'route' => 'recipes.index'],
            ['label' => $this->titlePage]
        ]"
    />

     {{-- toast de mensaje --}}
     <x-libraries.flux.toast-success />

    {{-- cuadricula --}}
    <div class="relative shadow-md sm:rounded-lg">
        <div class="flex flex-wrap justify-center gap-1 px-1 py-3">
            
            @foreach ($this->recipes as $item)
            <a 
                href="{{ route('recipes.show', ['recipeUuid' => $item->uuid]) }}"
            >
                <div class="relative w-20 h-20 sm:w-40 sm:h-40 rounded-lg overflow-hidden shadow-lg group">
                    <img src="{{ $item->cover_image_url }}" alt="Portada del libro" class="w-full h-full object-cover">
                    <div class="absolute inset-0 bg-black bg-opacity-50 opacity-0 group-hover:opacity-100 flex items-center justify-center transition-opacity duration-300">
                        <div class="text-center space-y-10">
                            <p class="text-white text-lg font-semibold px-2 text-center">{{ $item->title }}</p>
                            <p class="relative text-xs italic text-gray-700 dark:text-gray-300">
                                {{ $item->summary_clear ? '🗒️' : ''}}
                                {{ $item->notes_clear ? '✍️' : ''}}
                            </p>
                        </div>
                    </div>
                </div>
            </a>

            @endforeach
            
        </div>
    </div>
</div>