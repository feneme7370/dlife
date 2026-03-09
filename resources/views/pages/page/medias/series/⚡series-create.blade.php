<?php

use App\Models\Page\Serie;
use App\Models\Page\Mgenre;
use App\Models\Page\Collection;
use App\Models\Page\Subject;
use Livewire\Component;

new class extends Component
{
    //////////////////////////////////////////////////////////////////// PROPIEDADES PRINCIPALES
    //propiedades de titulos
    public string $title_serie = 'Agregar serie';
    public string $subtitle = 'Agregue un serie a la lista';

    // propiedades del item
    public string $title = '';
    public string $slug = '';
    public string $original_title = '';
    public string $synopsis = '';
    public int $start_date = 1;
    public int $end_date;
    public float $number_collection = 1;
    public int $seasons = 1;
    public int $episodes = 1;
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
    public $selectedMgenres = [];
    public $selectedSerieTags = [];

    // propiedades para lecturas
    public $start_view = null;
    public $end_view = null;

    //////////////////////////////////////////////////////////////////// VALIDACIONES
    // reglas de validacion
    protected function rules(){
        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', \Illuminate\Validation\Rule::unique('series', 'slug')->ignore($this->serie?->id ?? 0)],
            'original_title' => ['nullable', 'string', 'max:255'],
            'synopsis' => ['nullable', 'string'],
            'start_date' => ['nullable', 'integer', 'min:1'],
            'end_date' => ['nullable', 'integer', 'min:1'],
            'number_collection' => ['required', 'numeric', 'min:0'],
            'seasons' => ['nullable', 'integer', 'min:1'],
            'episodes' => ['nullable', 'integer', 'min:1'],
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
        return Mgenre::where('user_id', \Illuminate\Support\Facades\Auth::id())
            ->orderBy('name_general', 'asc')
            ->get();
    }

    // traer datos de colecciones para asociar
    public function collections(){
        return Collection::where('user_id', \Illuminate\Support\Facades\Auth::id())
            ->orderBy('name', 'asc')
            ->get();
    }

    // traer datos de generos para asociar
    public function subjects(){
        return Subject::where('user_id', \Illuminate\Support\Facades\Auth::id())
            ->orderBy('name', 'asc')
            ->get();
    }

    //////////////////////////////////////////////////////////////////// STORE PARA CREAR
    // crear item en la BD
    public function storeItem(){
        // datos automaticos
        $this->user_id = \Illuminate\Support\Facades\Auth::id();
        $this->slug = \Illuminate\Support\Str::slug($this->title . '-' . \Illuminate\Support\Str::random(4));
        $this->uuid = \Illuminate\Support\Str::random(24);
        $this->summary_clear = $this->cleanNotes($this->summary);
        $this->notes_clear = $this->cleanNotes($this->notes);

        // validar
        $validatedData = $this->validate();

        // crear en BD
        $serie = Serie::create($validatedData);
        $serie->subjects()->sync($this->selectedSerieSubjects);
        $serie->collections()->sync($this->selectedSerieCollections);
        $serie->genres()->sync($this->selectedMgenres);

        // agregar read de libro
        if($this->start_view || $this->end_view){
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
        $serie->tags()->sync($tagIds);

        session()->flash('success', 'Creado correctamente');

        // redireccionar
        $this->redirectRoute('series.index', navigate:true);
    }

    //////////////////////////////////////////////////////////////////// STORE PARA DATOS DE ASOCIACION ADICIONALES
    // store para crear una coleccion
    public $name_collection;
    public $books_amount_collection;
    public $movies_amount_collection;
    public $seasons_amount_collection;
    public function storeCollection(){
        \Illuminate\Support\Str::title(trim($this->name_collection));
        $this->start_view = $this->end_view;
        $this->validate([
            'name_collection' => ['required', 'string', 'max:255'],
            'books_amount_collection' => ['nullable', 'numeric'],
            'movies_amount_collection' => ['nullable', 'numeric'],
            'seasons_amount_collection' => ['nullable', 'numeric'],
        ]);

        $s = Collection::create([
            'name' => $this->name_collection,
            'books_amount' => $this->books_amount_collection ?? 0,
            'movies_amount' => $this->movies_amount_collection ?? 0,
            'seasons_amount' => $this->seasons_amount_collection ?? 0,
            'slug' => \Illuminate\Support\Str::slug(trim($this->name_collection) . '-' . \Illuminate\Support\Str::random(4)),
            'uuid' => \Illuminate\Support\Str::random(24),
            'user_id' => \Illuminate\Support\Facades\Auth::id(),
        ]);

        $this->reset('name_collection', 'books_amount_collection', 'series_amount_collection', 'seasons_amount_collection', 'selectedSerieCollections');
        $this->collections();
        $this->selectedSerieCollections[] = $s->id;
        $this->modal('add-collection')->close();
    }

    // store para crear un sujeto
    public $name_subject;
    public function storeSubject(){
        \Illuminate\Support\Str::title(trim($this->name_subject));
        $this->validate([
            'name_subject' => ['required', 'string', 'max:255'],
        ]);

        // crear en BD
        $s = Subject::create([
            'name' => $this->name_subject,
            'slug' => \Illuminate\Support\Str::slug(trim($this->name_subject) . '-' . \Illuminate\Support\Str::random(4)),
            'uuid' => \Illuminate\Support\Str::random(24),
            'user_id' => \Illuminate\Support\Facades\Auth::id(),
        ]);

        $this->reset('name_subject', 'selectedSerieSubjects');
        $this->collections();
        $this->selectedSerieSubjects[] = $s->id;
        $this->modal('add-subject')->close();
    }

    //////////////////////////////////////////////////////////////////// STORE PARA TAGS
    // store para crear tags
    public $name_tag;
    public $newTag = '';   // input actual

    public function addTag()
    {
        $formatted = str_replace(' ', '', \Illuminate\Support\Str::title(trim($this->newTag)));

        if ($formatted && !in_array($formatted, $this->selectedSerieTags)) {
            $this->selectedSerieTags[] = $formatted;
        }

        $this->newTag = '';
    }

    public function removeTag($index)
    {
        unset($this->selectedSerieTags[$index]);
        $this->selectedSerieTags = array_values($this->selectedSerieTags); // reindexa
    }

   public function cleanNotes(?string $html): string
    {
        if (!$html) return '';

        $text = str_replace(
            ['</p>', '<br>', '<br/>', '<br />'],
            "\n",
            $html
        );

        return trim(
            html_entity_decode(
                strip_tags($text),
                ENT_QUOTES | ENT_HTML5,
                'UTF-8'
            )
        );
    }
};
?>

<div>
    {{-- titulo, descripcion y breadcrumbs --}}
    <div>
        <div class="mb-1 space-y-1">
            <flux:heading size="xl" level="1">
                <a href="{{ route('series.index') }}"><flux:button size="xs" variant="ghost" icon="arrow-uturn-left"></flux:button></a>
                {{ $this->title_serie }}
            </flux:heading>
            <flux:text class="text-base">{{ $this->subtitle }}</flux:text>
    
            <flux:breadcrumbs>
                <flux:breadcrumbs.item href="{{ route('dashboard') }}">Dashboard</flux:breadcrumbs.item>
                <flux:breadcrumbs.item href="{{ route('series.index') }}">Series</flux:breadcrumbs.item>
                <flux:breadcrumbs.item>{{ $this->title_serie }}</flux:breadcrumbs.item>
            </flux:breadcrumbs>
    
            <flux:separator variant="subtle" />
        </div>
    </div>

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
            <flux:input wire:model='start_view' type="date" max="2999-12-31" label="Inicio de vista" />
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
                <flux:select wire:model="selectedSerieCollections">
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
                <flux:label>Autor</flux:label>
            </flux:modal.trigger>
        </div>

        <flux:checkbox.group wire:model.live="selectedSerieSubjects" :label="'Actor(es) '.count($selectedSerieSubjects)">
            <div class="h-40 overflow-scroll space-y-1">
                @foreach ($this->subjects() as $item)
                    <flux:checkbox label="{{ $item->name }}" value="{{ $item->id }}" />
                @endforeach
            </div>
        </flux:checkbox.group>

        <flux:input type="text" label="Etiquetas" wire:model="newTag" wire:keydown.space.prevent="addTag" placeholder="Agregue etiquetas" />
        <div class="flex gap-2 mt-2">
            @foreach($selectedSerieTags as $index => $tag)
                <flux:badge size="sm" color="purple">
                    <button class="mr-2" wire:click="removeTag({{ $index }})">
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


        <flux:modal name="add-collection" class="md:w-96">
            <div class="space-y-6">
                <div>
                    <flux:heading size="lg">Crear saga</flux:heading>
                    <flux:text class="mt-2">Cree una saga que no este en el listado.</flux:text>
                </div>

                <flux:input label="Nombre" placeholder="Nombre de la saga" wire:model="name_collection" autofocus/>
                <flux:input type="number" label="Numero de libros" placeholder="Cantidad de libros" wire:model="books_amount_collection"/>
                <flux:input type="number" label="Numero de serie" placeholder="Cantidad de serie" wire:model="movies_amount_collection"/>
                <flux:input type="number" label="Numero de temporadas" placeholder="Cantidad de temporadas" wire:model="seasons_amount_collection"/>

                <div class="flex">
                    <flux:spacer />

                @if ($errors->any())
                    <div class="bg-red-100 border border-red-400 text-red-700 p-1 rounded">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                    <flux:button wire:click="storeCollection" variant="primary">Agregar</flux:button>
                </div>
            </div>
        </flux:modal>

        <flux:modal name="add-subject" class="md:w-96">
            <div class="space-y-6">
                <div>
                    <flux:heading size="lg">Crear actor</flux:heading>
                    <flux:text class="mt-2">Cree un actor que no este en el listado.</flux:text>
                </div>

                <flux:input label="Nombre" placeholder="Nombre del actor" wire:model="name_subject" autofocus/>

                <div class="flex">
                    <flux:spacer />

                @if ($errors->any())
                    <div class="bg-red-100 border border-red-400 text-red-700 p-1 rounded">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                    <flux:button wire:click="storeSubject" variant="primary">Agregar</flux:button>
                </div>
            </div>
        </flux:modal>

</div>