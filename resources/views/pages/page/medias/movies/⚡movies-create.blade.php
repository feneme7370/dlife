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
    public string $titlePage = 'Agregar peliculas';
    public string $subtitlePage = 'Agregue un peliculas a la lista';

    // propiedades del item
    public string $title = '';
    public string $slug = '';
    public string $original_title = '';
    public string $synopsis = '';
    public int $release_date = 2;
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

    //////////////////////////////////////////////////////////////////// VALIDACIONES
    // reglas de validacion
    protected function rules(){
        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', \Illuminate\Validation\Rule::unique('movies', 'slug')->ignore($this->movie?->id ?? 0)],
            'original_title' => ['nullable', 'string', 'max:255'],
            'synopsis' => ['nullable', 'string'],
            'release_date' => ['nullable', 'integer', 'min:1'],
            'number_collection' => ['required', 'numeric', 'min:0'],
            'runtime' => ['nullable', 'integer', 'min:1'],
            'type' => ['nullable', 'integer', 'min:1'],

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
        'runtime' => 'minutos',
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

    //////////////////////////////////////////////////////////////////// STORE PARA CREAR
    // crear item en la BD
    public function storeItem(){
        // datos automaticos
        $this->user_id = \Illuminate\Support\Facades\Auth::id();
        $this->title = \Illuminate\Support\Str::title(trim($this->title));
        $this->slug = \Illuminate\Support\Str::slug($this->title . '-' . \Illuminate\Support\Str::random(4));
        $this->uuid = \Illuminate\Support\Str::random(24);
        $this->summary_clear = $this->cleanNotes($this->summary);
        $this->notes_clear = $this->cleanNotes($this->notes);
        $this->start_view = $this->end_view;

        // validar
        $validatedData = $this->validate();

        // crear en BD
        $movie = Movie::create($validatedData);
        $movie->subjects()->sync($this->selectedMovieSubjects);
        $movie->collections()->sync($this->selectedMovieCollections);
        $movie->genres()->sync($this->selectedMgenres);

        // agregar read de libro
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

        session()->flash('success', 'Creado correctamente');

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

        <div class="grid grid-cols-2 gap-1">
            {{-- <flux:input wire:model='start_view' type="date" max="2999-12-31" label="Inicio de vista" /> --}}
            <flux:input wire:model='end_view' type="date" max="2999-12-31" label="Vista" />
        </div>

        <flux:select wire:model="selectedMgenres" label="Genero">
            <option value="">Seleccionar genero</option>
            @foreach ($this->genres() as $item)
                <option value="{{ $item->id }}">{{ $item->name_general }} - {{ $item->name }}</option>
            @endforeach
        </flux:select>

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
        rows="15" 
        placeholder="{{ __('Resumen personal') }}" model="summary"
        model_data="{{ $summary }}" 
        />
        
        <x-libraries.quill-textarea-form 
            id_quill="editor_create_notes" 
            name="notes"
            rows="15" 
            placeholder="{{ __('Reseña') }}" model="notes"
            model_data="{{ $notes }}" 
        />

        @if ($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 p-1 rounded">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <flux:button icon="plus" wire:click="storeItem">Agregar</flux:button>
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

</div>