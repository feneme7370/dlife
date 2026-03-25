<?php

use App\Models\Page\Serie;
use App\Models\Page\Genre;
use App\Models\Page\Collection;
use App\Models\Page\Subject;
use Livewire\Component;

new class extends Component
{
    use \App\Traits\HandlesTags;
    use \App\Traits\CleansHtml;
    use \App\Traits\WithCollections;
    use \App\Traits\WithSubjects;
    
    //////////////////////////////////////////////////////////////////// PROPIEDADES PRINCIPALES
    //propiedades de titulos
    public string $titlePage = '';
    public string $subtitlePage = '';
    public string $buttonSubmit = '';

    // propiedades del item
    public string $title = '';
    public string $slug = '';
    public string $original_title = '';
    public string $synopsis = '';
    public int $start_date = 1;
    public ?int $end_date;
    public float $number_collection = 1;
    public int $seasons = 1;
    public int $episodes = 1;
    public int $type = 1;
    public string $summary = '';
    public string $summary_clear = '';
    public string $notes = '';
    public string $notes_clear = '';
    public bool $is_favorite = false;
    public bool $is_abandonated = false;
    public int $rating = 0;
    public string $cover_image_url = '';
    public string $uuid = '';
    public int $user_id = 0;

    // propiedades para relacion muchos a muchos
    public $selectedSerieSubjects = [];
    public $selectedSerieCollections = [];
    public $selectedMoviesGenres = [];
    public $selectedSerieTags = [];

    // propiedades para lecturas
    public $start_view = null;
    public $end_view = null;

    // propiedades del item
    public $serie;
    public $views, $viewId;

            //////////////////////////////////////////////////////////////////// BUSCAR EN API TMDB LOS DATOS
    public $searchSerie = '';
    public $results = [];
    public $actors_recommended = [];
    public $genres_recommended = [];

    public function searchSeries()
    {
        if(strlen($this->searchSerie) < 3){
            $this->results = [];
            return;
        }

        $response = \Illuminate\Support\Facades\Http::get('https://api.themoviedb.org/3/search/tv', [
            'api_key' => env('API_TMDB_KEY'),
            'query' => $this->searchSerie,
            'language' => 'es-MX',
        ]);

        $this->results = collect($response->json()['results'])
            ->take(5)
            ->toArray();
    }
    public function selectSerie($id)
    {
        $response = \Illuminate\Support\Facades\Http::get("https://api.themoviedb.org/3/tv/$id", [
            'api_key' => env('API_TMDB_KEY'),
            'language' => 'es-MX',
        ]);
        $credits = \Illuminate\Support\Facades\Http::get("https://api.themoviedb.org/3/tv/$id/credits", [
            'api_key' => env('API_TMDB_KEY'),
            'language' => 'es-MX',
        ]);

        $selectedSerie = $response->json();

        // autocompletar campos
        $this->title = $selectedSerie['name'];
        $this->original_title = $selectedSerie['original_name'];
        $this->synopsis = $selectedSerie['overview'];
        $this->start_date = substr($selectedSerie['first_air_date'], 0, 4) ?? null;
        $this->end_date = substr($selectedSerie['last_air_date'], 0, 4) ?? null;
        $this->seasons = $selectedSerie['number_of_seasons'] ?? 1;
        $this->episodes = $selectedSerie['number_of_episodes'] ?? 1;
        $this->cover_image_url = 'https://image.tmdb.org/t/p/w500'.$selectedSerie['poster_path'];
        $this->actors_recommended = collect($credits->json()['cast'])->take(5)->pluck('name')->toArray();
        $this->genres_recommended = collect($selectedSerie['genres'])->take(5)->pluck('name')->toArray();
        // cerrar modal
        $this->modal('select-serie-api')->close();
    }

    //////////////////////////////////////////////////////////////////// VALIDACIONES
    // reglas de validacion
    protected function rules(){
        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', \Illuminate\Validation\Rule::unique('series', 'slug')->ignore($this->serie?->id ?? 0)],
            'original_title' => ['nullable', 'string', 'max:255'],
            'synopsis' => ['nullable', 'string'],
            'start_date' => ['required', 'integer', 'min:1'],
            'end_date' => ['nullable', 'integer', 'min:1'],
            'number_collection' => ['required', 'numeric', 'min:0'],
            'seasons' => ['required', 'integer', 'min:1'],
            'episodes' => ['required', 'integer', 'min:1'],
            'type' => ['required', 'integer', 'min:1'],
            'summary' => ['nullable', 'string'],
            'summary_clear' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
            'notes_clear' => ['nullable', 'string'],
            'is_favorite' => ['nullable', 'bool'],
            'is_abandonated' => ['nullable', 'bool'],
            'rating' => ['required', 'integer', 'min:0', 'max:5'],
            'cover_image_url' => ['nullable', 'url', 'max:65535'],
            'uuid' => ['required', 'string', 'max:255', \Illuminate\Validation\Rule::unique('series', 'uuid')->ignore($this->serie?->id ?? 0)],
            'user_id' => ['required', 'exists:users,id'],
        ];
    }

    // renombrar variables a castellano
    protected $validationAttributes = [
        'title' => 'titulo',
        'slug' => 'slug',
        'original_title' => 'titulo original',
        'synopsis' => 'sinopsis',
        'start_date' => 'año inicio',
        'end_date' => 'año fin',
        'number_collection' => 'número de coleccion',
        'seasons' => 'temporadas',
        'episodes' => 'episodios',
        'type' => 'tipo',
        'summary' => 'resumen personal',
        'summary' => 'resumen personal limpio',
        'notes' => 'notas',
        'notes' => 'notas limpias',
        'is_favorite' => 'favorito',
        'is_favorite' => 'abandonado',
        'rating' => 'valoracion',
        'cover_image_url' => 'URL de imagen de portada',
        'uuid' => 'UUID',
        'user_id' => 'usuario',
    ];

    //////////////////////////////////////////////////////////////////// DATOS PRECARGADOS
    // cargar datos del libro
    public function mount($serieUuid = null){
        // traer datos de libro
        $this->serie = Serie::where('user_id', \Illuminate\Support\Facades\Auth::id())
            ->with(['subjects', 'genres', 'collections', 'tags', 'views'])
            ->where('uuid', $serieUuid)->first();

        // titulos y textos dependiendo si se encuentra el item o no
        $this->titlePage = $this->serie ? 'Modificar serie' : 'Agregar serie';
        $this->subtitlePage = $this->serie ? 'Modificar datos de la serie' : 'Agregar datos de la serie';
        $this->buttonSubmit = $this->serie ? 'Modificar' : 'Agregar';

        if($this->serie){            
            // poner datos a las propiedades
            $this->title = $this->serie->title;
            $this->slug = $this->serie->slug;
            $this->original_title = $this->serie->original_title ?? '';
            $this->synopsis = $this->serie->synopsis ?? '';
            $this->start_date = $this->serie->start_date ?? null;
            $this->end_date = $this->serie->end_date ?? null;
            $this->number_collection = $this->serie->number_collection ?? 1;
            $this->seasons = $this->serie->seasons ?? 1;
            $this->episodes = $this->serie->episodes ?? 1;
            $this->summary = $this->serie->summary ?? '';
            $this->summary_clear = $this->serie->summary_clear ?? '';
            $this->notes = $this->serie->notes ?? '';
            $this->notes_clear = $this->serie->notes_clear ?? '';
            $this->is_favorite = $this->serie->is_favorite ?? false;
            $this->is_abandonated = $this->serie->is_abandonated ?? false;
            $this->rating = $this->serie->rating ?? 0;
            $this->cover_image_url = $this->serie->cover_image_url ?? '';
            $this->user_id = $this->serie->user_id;
            $this->uuid = $this->serie->uuid;
    
            // poner en arrays las asociaciones de m2m
            $this->selectedMoviesGenres = $this->serie->genres->pluck('id')->toArray() ?? [];
            $this->selectedSerieSubjects = $this->serie->subjects->pluck('id')->toArray() ?? [];
            $this->selectedSerieCollections = $this->serie->collections->pluck('id')->toArray() ?? [];
            $this->selectedSerieTags = $this->serie->tags->pluck('name')->toArray() ?? [];
        }
    }

    //////////////////////////////////////////////////////////////////// DATOS PARA ASOCIAR
    // traer datos de generos para asociar
    public function genres(){
        return Genre::where('genre_type', 'visual')->where('user_id', \Illuminate\Support\Facades\Auth::id())->orderBy('name', 'asc')->get();
    }
    // traer datos de colecciones para asociar
    public function collections(){
        return Collection::where('user_id', \Illuminate\Support\Facades\Auth::id())->orderBy('name', 'asc')->get();
    }
    // traer datos de generos para asociar
    public function subjects(){
        return Subject::where('user_id', \Illuminate\Support\Facades\Auth::id())->orderBy('name', 'asc')->get();
    }
    // traer tipos
    public function types(){return Serie::type();}

    //////////////////////////////////////////////////////////////////// MODAL PARA CARGAR READS
    // abrir modal de nota
    public function modalView(){
        $this->start_view = null;
        $this->end_view = null;
        $this->modal('add-read')->show();
    }

    // agregar lectura
    public function addView(){

        $this->validate([
            'start_view' => ['required', 'date'],
            'end_view' => ['required', 'date'],
        ]);

        \App\Models\Page\SerieView::create([
            'user_id' => \Illuminate\Support\Facades\Auth::id(),
            'serie_id' => $this->serie->id,
            'start_view' => $this->start_view,
            'end_view' => $this->end_view,
        ]);

        $this->views = \App\Models\Page\SerieView::where('serie_id', $this->serie->id)->get();
        $this->start_view = '';
        $this->end_view = '';

        $this->modal('add-read')->close();

        session()->flash('success', 'Vista agregada');
    }

    // modal para borrar nota
    public function deleteView($id){
        $this->viewId = $id;
        $this->modal('delete-read')->show();
    }
    // borrar nota
    public function destroyView(){
        \App\Models\Page\SerieView::find($this->viewId)->delete();
        $this->modal('delete-read')->close();
        $this->views = \App\Models\Page\SerieView::where('serie_id', $this->serie->id)->get();
        $this->viewId = '';
        session()->flash('success', 'Vista eliminada');
    }

    //////////////////////////////////////////////////////////////////// EDITAR DATOS
    // crear item en la BD
    public function updateItem(){
        // datos automaticos
        $this->title = \Illuminate\Support\Str::title(trim($this->title));
        $this->slug = \Illuminate\Support\Str::slug($this->title . '-' . \Illuminate\Support\Facades\Auth::id());
        $this->summary_clear = $this->cleanNotes($this->summary);
        $this->notes_clear = $this->cleanNotes($this->notes);
        
        if($this->serie){
            // validar
            $validatedData = $this->validate();
    
            // crear en BD
            $this->serie->update($validatedData);
            $this->serie->subjects()->sync($this->selectedSerieSubjects);
            $this->serie->collections()->sync($this->selectedSerieCollections);
            $this->serie->genres()->sync($this->selectedMoviesGenres);
    
            // agregar tags
            $tagIds = [];
            foreach ($this->selectedSerieTags as $tagName) {
                $tag = \App\Models\Page\Tag::firstOrCreate(
                    ['name' => $tagName],
                    [
                        'slug' => \Illuminate\Support\Str::slug($tagName),
                        'tag_type' => 'series',
                        'uuid' => \Illuminate\Support\Str::random(24),
                        'user_id' => \Illuminate\Support\Facades\Auth::id(),
                    ]
                );
    
                $tagIds[] = $tag->id;
            }
            $this->serie->tags()->sync($tagIds);
        }else{
            // datos automaticos
            $this->user_id = \Illuminate\Support\Facades\Auth::id();
            $this->uuid = \Illuminate\Support\Str::random(24);

            // validar
            $validatedData = $this->validate();

            // crear en BD
            $serie = Serie::create($validatedData);
            $serie->subjects()->sync($this->selectedSerieSubjects);
            $serie->collections()->sync($this->selectedSerieCollections);
            $serie->genres()->sync($this->selectedMoviesGenres);

            // agregar view
            if($this->start_view){
                \App\Models\Page\SerieView::create([
                    'user_id' => \Illuminate\Support\Facades\Auth::id(),
                    'serie_id' => $serie->id,
                    'start_view' => $this->start_view,
                    'end_view' => $this->end_view,
                ]);
            };

            // agregar tags
            $tagIds = [];
            foreach ($this->selectedSerieTags as $tagName) {
                $tag = \App\Models\Page\Tag::firstOrCreate(
                    ['name' => $tagName],
                    [
                        'slug' => \Illuminate\Support\Str::slug($tagName),
                        'tag_type' => 'series',
                        'uuid' => \Illuminate\Support\Str::random(24),
                        'user_id' => \Illuminate\Support\Facades\Auth::id(),
                    ]
                );

                $tagIds[] = $tag->id;
            }
            $serie->tags()->sync($tagIds);
        }

        // mensaje de success
        session()->flash('success', $this->serie ? 'Editado correctamente' : 'Creado correctamente');

        // redireccionar
        $this->redirectRoute('series.index', navigate:true);
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

    {{-- buscar serie en api --}}
    <div class="flex gap-2 items-center">
        <flux:modal.trigger name="select-serie-api">
            <flux:button size="xs" variant="ghost" icon="plus"></flux:button>
            <flux:label>Buscar Serie</flux:label>
        </flux:modal.trigger>
    </div>

    {{-- formulario completo --}}
    <div class="space-y-2">
        <flux:input type="text" label="Nombre" wire:model="title" placeholder="Nombre del libro" autofocus/>
        <flux:input type="text" label="Nombre Original" wire:model="original_title" placeholder="Nombre del libro original" />
        <flux:textarea
            label="Sinopsis"
            placeholder="Coloque la sinopsis"
            wire:model="synopsis"
            rows="6"
        />
        <div class="grid grid-cols-3 gap-1">
            <flux:input type="number" max="2999" min="1" label="Año inicio" wire:model="start_date"/>
            <flux:input type="number" max="2999" min="1" label="Año fin" wire:model="end_date"/>
        </div>
        <div class="grid grid-cols-3 gap-1">
            <flux:input type="number" max="9999" min="1" label="Temporada(s)" wire:model="seasons"/>
            <flux:input type="number" max="9999" min="1" label="Episodio(s)" wire:model="episodes"/>
            <flux:select wire:model="type" label="Tipo">
                <option value="">Seleccionar tipo</option>
                @foreach ($this->types() as $key => $item)
                    <option value="{{ $key }}">{{ $item }}</option>
                @endforeach
            </flux:select>
        </div>

        <flux:input type="text" label="Link de portada" wire:model="cover_image_url" placeholder="Pegue el link de una imagen"/>

        <div class="grid grid-cols-2 gap-1 my-5">
            <flux:field variant="inline" class="flex items-center">
                <flux:checkbox wire:model="is_favorite" />

                <flux:label>Favorito? ❤️</flux:label>

                <flux:error name="is_favorite" />
            </flux:field>
            <flux:field variant="inline" class="flex items-center">
                <flux:checkbox wire:model="is_abandonated" />

                <flux:label>Abandonado? 🚫</flux:label>

                <flux:error name="is_abandonated" />
            </flux:field>
        </div>

        <div class="mt-5">
            <flux:radio.group wire:model="rating">
                <flux:radio value="0" label="Sin valoracion" checked />
                <flux:radio value="1" label="⭐" />
                <flux:radio value="2" label="⭐⭐" />
                <flux:radio value="3" label="⭐⭐⭐" />
                <flux:radio value="4" label="⭐⭐⭐⭐" />
                <flux:radio value="5" label="⭐⭐⭐⭐⭐" />
            </flux:radio.group>
        </div>

        @if ($serie)
            <div class="flex gap-2 items-center">
                <flux:button wire:click="modalView" class="mt-1" size="sm" variant="ghost" color="purple" icon="plus" type="submit"></flux:button>
                <flux:separator text="Vistas" />
            </div>
        @else
            <div class="grid grid-cols-2 gap-1">
                <flux:input wire:model='start_view' type="date" max="2999-12-31" label="Inicio de vista" />
                <flux:input wire:model='end_view' type="date" max="2999-12-31" label="Vista" />
            </div>
        @endif

        @if ($serie)
            @foreach ($serie->views as $item)
            <div class="flex items-start justify-between">
                <div class="px-3 border-l-4 border-purple-800">
                    @if ($item->end_view)
                        <p class="mb-2 text-xs sm:text-base text-gray-800 dark:text-gray-300 ">{{ \Carbon\Carbon::parse($item->start_view)->format('Y-m-d') }} - {{ \Carbon\Carbon::parse($item->end_view)->format('Y-m-d') }} en {{ \Carbon\Carbon::parse($item->start_view)->diffInDays($item->end_view) }} dias</p>
                    @else
                        <p class="mb-2 text-xs sm:text-base text-gray-800 dark:text-gray-300 ">{{ \Carbon\Carbon::parse($item->start_view)->format('Y-m-d') }} Viendo...</p>
                    @endif
                </div>

                <flux:button wire:click="deleteView({{ $item->id }})" class="ml-3 text-gray-400 hover:text-red-500 transition" size="sm" variant="ghost" color="purple" type="submit">✕</flux:button>
            </div>
            @endforeach
        @endif

        <div>
            <div class="flex items-center mb-1">
                <flux:label>Generos {{ count($selectedMoviesGenres) }}</flux:label>
            </div>
            <flux:checkbox.group wire:model.live="selectedMoviesGenres">
                <div class="grid grid-cols-2 md:grid-cols-3 h-max-96 overflow-y-scroll space-y-1">
                    @foreach ($this->genres() as $item)
                        <flux:checkbox label="{{ $item->name }}" value="{{ $item->id }}" />
                    @endforeach
                </div>
            </flux:checkbox.group>
        </div>
        @if ($this->genres_recommended)
            <p class="text-xs italic">Recomendado: {{ implode(', ', $this->genres_recommended) }}</p>
        @endif

        <div class="grid grid-cols-12 gap-1">
            <div class="col-span-10 space-y-1">
                <div class="flex items-center gap-1">
                    <flux:modal.trigger name="add-collection">
                        <flux:button size="xs" variant="ghost" icon="plus"></flux:button>
                    </flux:modal.trigger>
                    <flux:label>Saga</flux:label>
                </div>
                <flux:select wire:model="selectedSerieCollections">
                    <option value="">Seleccionar saga</option>
                    @foreach ($this->collections() as $item)
                        <option value="{{ $item->id }}">{{ $item->name }}</option>
                    @endforeach
                </flux:select>
            </div>
            <div class="col-span-2 space-y-1">
                <div>
                    <flux:label>N° saga</flux:label>
                </div>
                <flux:input type="number" max="2999" min="0" step="0.1" wire:model="number_collection"/>
            </div>
        </div>

        <div class="flex items-center gap-1">
            <flux:modal.trigger name="add-subject">
                <flux:button size="xs" variant="ghost" icon="plus"></flux:button>
                <flux:label>Actor(es) {{ count($selectedSerieSubjects) }}</flux:label>
            </flux:modal.trigger>
        </div>
 
        <div class="col-span-12 sm:col-span-6">
            <x-libraries.flux.select-multiple
                model="subject" 
                relation="subjects" 
                wire:model="selectedSerieSubjects" 
                {{-- label="Autores" --}}
                :items="$this->subjects()"
            />
        </div>
        @if ($this->actors_recommended)
            <p class="text-xs italic">Recomendado: {{ implode(', ', $this->actors_recommended) }}</p>
        @endif

        <flux:label>Etiquetas</flux:label>
        <flux:input.group>
            <flux:input type="text" wire:model="newTag" wire:keydown.period.prevent="addTag('selectedSerieTags')" placeholder="Agregue etiquetas" />
            <flux:button wire:click="addTag('selectedSerieTags')" icon="plus">Agregar</flux:button>
        </flux:input.group>

        <div class="flex gap-2 mt-2 w-full flex-wrap">
            @foreach($selectedSerieTags as $index => $tag)
                <flux:badge size="sm" color="purple">
                    <button class="mr-2" wire:click="removeTag('selectedSerieTags', {{ $index }})">
                        x
                    </button>
                    #{{ $tag }}
                </flux:badge>
            @endforeach
        </div>

        <flux:label>Resumen personal</flux:label>
        <x-libraries.quill-textarea-form
        id_quill="editor_create_summary"
        name="summary"
        height="400"
        placeholder="{{ __('Resumen personal') }}" model="summary"
        model_data="{{ $summary }}"
        />

        <flux:label>Reseña</flux:label>
        <x-libraries.quill-textarea-form
            id_quill="editor_create_notes"
            name="notes"
            height="300"
            placeholder="{{ __('Reseña') }}" model="notes"
            model_data="{{ $notes }}"
        />

        <x-libraries.utilities.errors />

        <flux:button :icon="$serie ? 'pencil-square' : 'plus'" wire:click="updateItem">{{ $this->buttonSubmit }}</flux:button>
    </div>

    {{-- modal para agregar coleccion --}}
    <flux:modal name="add-collection" class="md:w-96">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Crear saga</flux:heading>
                <flux:text class="mt-2">Cree una saga que no este en el listado.</flux:text>
            </div>

            <flux:input label="Nombre" placeholder="Nombre de la saga" wire:model="name_collection" autofocus/>
            <flux:input type="number" label="Numero de libros" placeholder="Cantidad de libros" wire:model="books_amount_collection"/>
            <flux:input type="number" label="Numero de peliculas" placeholder="Cantidad de peliculas" wire:model="movies_amount_collection"/>
            <flux:input type="number" label="Numero de temporadas" placeholder="Cantidad de temporadas" wire:model="seasons_amount_collection"/>

            <div class="flex">
                <flux:spacer />

                <x-libraries.utilities.errors />

                <flux:button wire:click="storeCollection('selectedSerieCollections')" variant="primary">Agregar</flux:button>
            </div>
        </div>
    </flux:modal>

    {{-- modal para agregar sujeto --}}
    <flux:modal name="add-subject" class="md:w-96">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Crear actor</flux:heading>
                <flux:text class="mt-2">Cree un actor que no este en el listado.</flux:text>
            </div>

            <flux:input label="Nombre" placeholder="Nombre del actor" wire:model="name_subject" autofocus/>

            <div class="flex">
                <flux:spacer />

                <x-libraries.utilities.errors />

                <flux:button wire:click="storeSubject('selectedSerieSubjects')" variant="primary">Agregar</flux:button>
            </div>
        </div>
    </flux:modal>

    {{-- modales para agrear y eliminar lecturas --}}
    <flux:modal name="add-read" class="md:w-96">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Vista</flux:heading>
                <flux:text class="mt-2">Agregue una fecha de vista.</flux:text>
            </div>
            <div class="grid grid-cols-2 gap-1">
                <flux:input wire:model='start_view' type="date" max="2999-12-31" label="Inicio de vista" />
                <flux:input wire:model='end_view' type="date" max="2999-12-31" label="Fin de Vista" />
            </div>
            <div class="flex gap-2">
                <flux:spacer />

                <flux:modal.close>
                    <flux:button variant="ghost">Cancelar</flux:button>
                </flux:modal.close>

                <flux:button wire:click="addView" type="submit" variant="primary">Editar</flux:button>
            </div>
        </div>
    </flux:modal>

    <flux:modal name="delete-read" class="min-w-[22rem]">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Eliminar</flux:heading>

                <flux:text class="mt-2">
                    <p>Desea eliminar esta lectura?.</p>
                    <p>Esta accion no puede revertirse.</p>
                </flux:text>
            </div>

            <div class="flex gap-2">
                <flux:spacer />

                <flux:modal.close>
                    <flux:button variant="ghost">Cancelar</flux:button>
                </flux:modal.close>

                <flux:button wire:click="destroyView" type="submit" variant="danger">Borrar</flux:button>
            </div>
        </div>
    </flux:modal>

    {{-- modales seleccionar serie en api --}}
    <flux:modal name="select-serie-api" class="md:w-96">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Buscar Serie</flux:heading>
                <flux:text class="mt-2">Busque los datos de una serie.</flux:text>
            </div>

            <div class="grid gap-1">
                <div>
                    <flux:input.group>
                        <flux:input 
                            wire:model.live.debounce.500ms="searchSerie"
                            placeholder="Buscar serie..."
                        />
                        <flux:button wire:click="searchSeries" icon="magnifying-glass"></flux:button>
                    </flux:input.group>
                <div class="space-y-2 mt-4">

                    @foreach($results as $item)
                        <div 
                            wire:click="selectSerie({{ $item['id'] }})"
                            class="flex gap-3 p-2 cursor-pointer hover:bg-zinc-100 dark:hover:bg-zinc-800 rounded"
                        >
                            <img 
                                src="https://image.tmdb.org/t/p/w200{{ $item['poster_path'] }}" 
                                class="w-12 h-16 object-cover rounded"
                            />

                            <div>
                                <div class="font-semibold">
                                    {{ $item['name'] }}
                                </div>

                                <div class="text-xs text-zinc-500">
                                    {{ $item['first_air_date'] ?? '' }}
                                </div>
                                <div class="text-xs text-zinc-500">
                                    {{ $item['end_air_date'] ?? '' }}
                                </div>
                            </div>
                        </div>
                    @endforeach

                </div>
                
                <div class="flex gap-2">
                    <flux:spacer />
    
                    <flux:modal.close>
                        <flux:button variant="ghost">Cancelar</flux:button>
                    </flux:modal.close>
                </div>
            </div>
      

        </div>
    </flux:modal>
</div>