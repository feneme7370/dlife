<?php

use App\Models\Page\Movie;
use App\Models\Page\Mgenre;
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

    public string $title = '';
    public string $slug = '';
    public string $original_title = '';
    public string $synopsis = '';
    public int $release_date = 1;
    public float $number_collection = 1;
    public int $runtime = 1;
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
    public $selectedMovieSubjects = [];
    public $selectedMovieCollections = [];
    public $selectedMgenres = [];
    public $selectedMovieTags = [];

    // propiedades para lecturas
    public $start_view = null;
    public $end_view = null;

    // propiedades del item
    public $movie;
    public $views, $viewId;

        //////////////////////////////////////////////////////////////////// BUSCAR EN API TMDB LOS DATOS
    public $searchMovie = '';
    public $results = [];
    public $actors_recommended = [];
    public $genres_recommended = '';

    public function searchMovies()
    {
        if(strlen($this->searchMovie) < 3){
            $this->results = [];
            return;
        }

        $response = \Illuminate\Support\Facades\Http::get('https://api.themoviedb.org/3/search/movie', [
            'api_key' => env('API_TMDB_KEY'),
            'query' => $this->searchMovie,
            'language' => 'es-ES',
        ]);

        $this->results = collect($response->json()['results'])
            ->take(5)
            ->toArray();
    }
    public function selectMovie($id)
    {
        $response = \Illuminate\Support\Facades\Http::get("https://api.themoviedb.org/3/movie/$id", [
            'api_key' => env('API_TMDB_KEY'),
            'language' => 'es-ES',
        ]);
        $credits = \Illuminate\Support\Facades\Http::get("https://api.themoviedb.org/3/movie/$id/credits", [
            'api_key' => env('API_TMDB_KEY'),
            'language' => 'es-ES',
        ]);

        $selectedMovie = $response->json();
        // autocompletar campos
        $this->title = $selectedMovie['title'];
        $this->original_title = $selectedMovie['original_title'];
        $this->synopsis = $selectedMovie['overview'];
        $this->release_date = substr($selectedMovie['release_date'], 0, 4);
        $this->runtime = $selectedMovie['runtime'];
        $this->cover_image_url = 'https://image.tmdb.org/t/p/w500'.$selectedMovie['poster_path'];
        $this->actors_recommended = collect($credits->json()['cast'])->take(5)->pluck('name')->toArray();
        $this->genres_recommended = collect($selectedMovie['genres'])->take(5)->pluck('name')->toArray();

        // cerrar modal
        $this->modal('select-movie-api')->close();
    }
    //////////////////////////////////////////////////////////////////// VALIDACIONES
    // reglas de validacion
    protected function rules(){
        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', \Illuminate\Validation\Rule::unique('movies', 'slug')->ignore($this->movie?->id ?? 0)],
            'original_title' => ['nullable', 'string', 'max:255'],
            'synopsis' => ['nullable', 'string'],
            'release_date' => ['required', 'integer', 'min:1'],
            'number_collection' => ['required', 'numeric', 'min:0'],
            'runtime' => ['required', 'integer', 'min:1'],
            'type' => ['required', 'integer', 'min:1'],
            'summary' => ['nullable', 'string'],
            'summary_clear' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
            'notes_clear' => ['nullable', 'string'],
            'is_favorite' => ['nullable', 'bool'],
            'is_abandonated' => ['nullable', 'bool'],
            'rating' => ['required', 'integer', 'min:0', 'max:5'],
            'cover_image_url' => ['nullable', 'url', 'max:65535'],
            'uuid' => ['required', 'string', 'max:255', \Illuminate\Validation\Rule::unique('movies', 'uuid')->ignore($this->movie?->id ?? 0)],
            'user_id' => ['required', 'exists:users,id'],
        ];
    }

    // renombrar variables a castellano
    protected $validationAttributes = [
        'title' => 'titulo',
        'slug' => 'slug',
        'original_title' => 'titulo original',
        'synopsis' => 'sinopsis',
        'release_date' => 'publicacion',
        'number_collection' => 'número de coleccion',
        'runtime' => 'duracion',
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
    public function mount($movieUuid = null){
        // traer datos de libro
        $this->movie = Movie::where('user_id', \Illuminate\Support\Facades\Auth::id())
            ->with(['subjects', 'genres', 'collections', 'tags', 'views'])
            ->where('uuid', $movieUuid)->first();

        // titulos y textos dependiendo si se encuentra el item o no
        $this->titlePage = $this->movie ? 'Modificar pelicula' : 'Agregar pelicula';
        $this->subtitlePage = $this->movie ? 'Modificar datos de la pelicula' : 'Agregar datos de la pelicula';
        $this->buttonSubmit = $this->movie ? 'Modificar' : 'Agregar';

        if($this->movie){
            // poner datos a las propiedades
            $this->title = $this->movie->title;
            $this->slug = $this->movie->slug;
            $this->original_title = $this->movie->original_title ?? '';
            $this->synopsis = $this->movie->synopsis ?? '';
            $this->release_date = $this->movie->release_date ?? 1;
            $this->number_collection = $this->movie->number_collection ?? 1;
            $this->runtime = $this->movie->runtime ?? 1;
            $this->type = $this->movie->type ?? 1;
            $this->summary = $this->movie->summary ?? '';
            $this->summary_clear = $this->movie->summary_clear ?? '';
            $this->notes = $this->movie->notes ?? '';
            $this->notes_clear = $this->movie->notes_clear ?? '';
            $this->is_favorite = $this->movie->is_favorite ?? false;
            $this->is_abandonated = $this->movie->is_abandonated ?? false;
            $this->rating = $this->movie->rating ?? 0;
            $this->cover_image_url = $this->movie->cover_image_url ?? '';
            $this->user_id = $this->movie->user_id;
            $this->uuid = $this->movie->uuid;
    
            // poner en arrays las asociaciones de m2m
            $this->selectedMgenres = $this->movie->genres->pluck('id')->toArray() ?? [];
            $this->selectedMovieSubjects = $this->movie->subjects->pluck('id')->toArray() ?? [];
            $this->selectedMovieCollections = $this->movie->collections->pluck('id')->toArray() ?? [];
            $this->selectedMovieTags = $this->movie->tags->pluck('name')->toArray() ?? [];
        }
    }

    //////////////////////////////////////////////////////////////////// DATOS PARA ASOCIAR
    // traer datos de generos para asociar
    public function genres(){
        return Mgenre::where('user_id', \Illuminate\Support\Facades\Auth::id())->orderBy('name_general', 'asc')->get();
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
    public function types(){return Movie::type();}

    //////////////////////////////////////////////////////////////////// MODAL PARA CARGAR READS
    // abrir modal de nota
    public function modalView(){
        $this->start_view = null;
        $this->end_view = null;
        $this->modal('add-read')->show();
    }

    // agregar lectura
    public function addView(){

        $this->start_view = $this->end_view;
        $this->validate([
            'start_view' => ['required', 'date'],
            'end_view' => ['required', 'date'],
        ]);

        \App\Models\Page\MovieView::create([
            'user_id' => \Illuminate\Support\Facades\Auth::id(),
            'movie_id' => $this->movie->id,
            'start_view' => $this->start_view,
            'end_view' => $this->end_view,
        ]);

        $this->views = \App\Models\Page\MovieView::where('movie_id', $this->movie->id)->get();
        $this->start_view = '';
        $this->end_view = '';

        $this->modal('add-read')->close();

        session()->flash('success', 'Lectura agregada');
    }

    // modal para borrar nota
    public function deleteView($id){
        $this->viewId = $id;
        $this->modal('delete-read')->show();
    }
    // borrar nota
    public function destroyView(){
        \App\Models\Page\MovieView::find($this->viewId)->delete();
        $this->modal('delete-read')->close();
        $this->views = \App\Models\Page\MovieView::where('movie_id', $this->movie->id)->get();
        $this->viewId = '';
        session()->flash('success', 'Lecura eliminada');
    }

    //////////////////////////////////////////////////////////////////// EDITAR DATOS
    // crear item en la BD
    public function updateItem(){
        // datos automaticos
        $this->title = \Illuminate\Support\Str::title(trim($this->title));
        $this->slug = \Illuminate\Support\Str::slug($this->title . '-' . \Illuminate\Support\Str::random(4));
        $this->summary_clear = $this->cleanNotes($this->summary);
        $this->notes_clear = $this->cleanNotes($this->notes);
        $this->start_view = $this->end_view;

        if($this->movie){
            // validar
            $validatedData = $this->validate();

            // crear en BD
            $this->movie->update($validatedData);
            $this->movie->subjects()->sync($this->selectedMovieSubjects);
            $this->movie->collections()->sync($this->selectedMovieCollections);
            $this->movie->genres()->sync($this->selectedMgenres);

            // agregar tags
            $tagIds = [];
            foreach ($this->selectedMovieTags as $tagName) {
                $tag = \App\Models\Page\Mtag::firstOrCreate(
                    ['name' => $tagName],
                    [
                        'slug' => \Illuminate\Support\Str::slug($tagName),
                        'uuid' => \Illuminate\Support\Str::random(24),
                        'user_id' => \Illuminate\Support\Facades\Auth::id(),
                    ]
                );

                $tagIds[] = $tag->id;
            }
            $this->movie->tags()->sync($tagIds);
        }else{
            // datos automaticos
            $this->user_id = \Illuminate\Support\Facades\Auth::id();
            $this->uuid = \Illuminate\Support\Str::random(24);

            // validar
            $validatedData = $this->validate();

            // crear en BD
            $movie = Movie::create($validatedData);
            $movie->subjects()->sync($this->selectedMovieSubjects);
            $movie->collections()->sync($this->selectedMovieCollections);
            $movie->genres()->sync($this->selectedMgenres);

            // agregar view
            if($this->start_view || $this->end_view){
                \App\Models\Page\MovieView::create([
                    'user_id' => \Illuminate\Support\Facades\Auth::id(),
                    'movie_id' => $movie->id,
                    'start_view' => $this->start_view,
                    'end_view' => $this->end_view,
                ]);
            };

            // agregar tags
            $tagIds = [];
            foreach ($this->selectedMovieTags as $tagName) {
                $tag = \App\Models\Page\Mtag::firstOrCreate(
                    ['name' => $tagName],
                    [
                        'slug' => \Illuminate\Support\Str::slug($tagName),
                        'uuid' => \Illuminate\Support\Str::random(24),
                        'user_id' => \Illuminate\Support\Facades\Auth::id(),
                    ]
                );

                $tagIds[] = $tag->id;
            }
            $movie->tags()->sync($tagIds);
        }

        // mensaje de success
        session()->flash('success', $this->movie ? 'Editado correctamente' : 'Creado correctamente');

        // redireccionar
        $this->redirectRoute('movies.index', navigate:true);
    }
};
?>

<div>

    {{-- titulo, descripcion y breadcrumbs --}}
    <div>
        <div class="mb-1 space-y-1">
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
        </div>
    </div>
    
    {{-- buscar pelicula en api --}}
    <div class="flex gap-2 items-center">
        <flux:modal.trigger name="select-movie-api">
            <flux:button size="xs" variant="ghost" icon="plus"></flux:button>
            <flux:label>Buscar Pelicula</flux:label>
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
            <flux:input type="number" max="2999" min="1" label="Año de publicacion" wire:model="release_date"/>
            <flux:input type="number" max="9999" min="1" label="Duracion" wire:model="runtime"/>
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

        @if ($movie)
            <div class="flex gap-2 items-center">
                <flux:button wire:click="modalView" class="mt-1" size="sm" variant="ghost" color="purple" icon="plus" type="submit"></flux:button>
                <flux:separator text="Vistas" />
            </div>
        @else
            <div class="grid grid-cols-2 gap-1">
                <flux:input wire:model='end_view' type="date" max="2999-12-31" label="Vista" />
            </div>
        @endif

        @if($movie)
            @foreach ($movie->views as $item)
            <div class="flex items-start justify-between">
                <div class="px-3 border-l-4 border-purple-800">
                    @if ($item->end_view)
                        <p class="mb-2 text-xs sm:text-base text-gray-800 dark:text-gray-300 ">{{ \Carbon\Carbon::parse($item->end_view)->format('Y-m-d') }}</p>
                    @endif
                </div>

                <flux:button wire:click="deleteView({{ $item->id }})" class="ml-3 text-gray-400 hover:text-red-500 transition" size="sm" variant="ghost" color="purple" type="submit">✕</flux:button>
            </div>
            @endforeach
        @endif

        <flux:select wire:model="selectedMgenres" label="Genero">
            <option value="">Seleccionar genero</option>
            @foreach ($this->genres() as $item)
                <option value="{{ $item->id }}">{{ $item->name_general }} - {{ $item->name }}</option>
            @endforeach
        </flux:select>
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
                <flux:select wire:model="selectedMovieCollections">
                    <option value="">Seleccionar saga</option>
                    @foreach ($this->collections() as $item)
                        <option value="{{ $item->id }}">{{ $item->name }}</option>
                    @endforeach
                </flux:select>
            </div>
            <div class="col-span-2 space-y-1">
                <div>
                    <flux:label>N° de coleccion</flux:label>
                </div>
                <flux:input type="number" max="2999" min="0" step="0.1" wire:model="number_collection"/>
            </div>
        </div>

        <div class="flex items-center gap-1">
            <flux:modal.trigger name="add-subject">
                <flux:button size="xs" variant="ghost" icon="plus"></flux:button>
                <flux:label>Actor(es) {{ count($selectedMovieSubjects) }}</flux:label>
            </flux:modal.trigger>
        </div>
 
        <div class="col-span-12 sm:col-span-6">
            <x-libraries.flux.select-multiple
                model="subject" 
                relation="subjects" 
                wire:model="selectedMovieSubjects" 
                {{-- label="Autores" --}}
                :items="$this->subjects()"
            />
        </div>
        @if ($this->actors_recommended)
            <p class="text-xs italic">Recomendado: {{ implode(', ', $this->actors_recommended) }}</p>
        @endif

        <flux:label>Etiquetas</flux:label>
        <flux:input.group>
            <flux:input type="text" wire:model="newTag" wire:keydown.period.prevent="addTag('selectedMovieTags')" placeholder="Agregue etiquetas" />
            <flux:button wire:click="addTag('selectedMovieTags')" icon="plus">Agregar</flux:button>
        </flux:input.group>

        <div class="flex gap-2 mt-2">
            @foreach($selectedMovieTags as $index => $tag)
                <flux:badge size="sm" color="purple">
                    <button class="mr-2" wire:click="removeTag('selectedMovieTags', {{ $index }})">
                        x
                    </button>
                    #{{ $tag }}
                </flux:badge>
            @endforeach
        </div>

        <x-libraries.quill-textarea-form
            id_quill="editor_create_summary"
            name="summary"
            height="400"
            placeholder="{{ __('Resumen personal') }}" model="summary"
            model_data="{{ $summary }}"
        />

        <x-libraries.quill-textarea-form
            id_quill="editor_create_notes"
            name="notes"
            height="300"
            placeholder="{{ __('Reseña') }}" model="notes"
            model_data="{{ $notes }}"
        />

        <x-libraries.utilities.errors />

        <flux:button :icon="$movie ? 'pencil-square' : 'plus'" wire:click="updateItem">{{ $this->buttonSubmit }}</flux:button>
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

                <flux:button wire:click="storeCollection('selectedMovieCollections')" variant="primary">Agregar</flux:button>
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

                <flux:button wire:click="storeSubject('selectedMovieSubjects')" variant="primary">Agregar</flux:button>
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
                {{-- <flux:input wire:model='start_view' type="date" max="2999-12-31" label="Inicio de lectura" /> --}}
                <flux:input wire:model='end_view' type="date" max="2999-12-31" label="Vista" />
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


    {{-- modales seleccionar peliculas en api --}}
    <flux:modal name="select-movie-api" class="md:w-96">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Buscar Pelicula</flux:heading>
                <flux:text class="mt-2">Busque los datos de una pelicula.</flux:text>
            </div>

            <div class="grid gap-1">
                <div>
                    <flux:input.group>
                        <flux:input 
                            wire:model.live.debounce.500ms="searchMovie"
                            placeholder="Buscar película..."
                        />
                        <flux:button wire:click="searchMovies" icon="magnifying-glass"></flux:button>
                    </flux:input.group>
                <div class="space-y-2 mt-4">

                    @foreach($results as $item)
                        <div 
                            wire:click="selectMovie({{ $item['id'] }})"
                            class="flex gap-3 p-2 cursor-pointer hover:bg-zinc-100 dark:hover:bg-zinc-800 rounded"
                        >
                            <img 
                                src="https://image.tmdb.org/t/p/w200{{ $item['poster_path'] }}" 
                                class="w-12 h-16 object-cover rounded"
                            />

                            <div>
                                <div class="font-semibold">
                                    {{ $item['title'] }}
                                </div>

                                <div class="text-xs text-zinc-500">
                                    {{ $item['release_date'] ?? '' }}
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