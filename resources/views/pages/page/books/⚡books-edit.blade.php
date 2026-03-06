<?php

use App\Models\Page\Book;
use App\Models\Page\BookGenre;
use App\Models\Page\Collection;
use App\Models\Page\Language;
use App\Models\Page\ReadingFormat;
use App\Models\Page\Subject;
use Livewire\Component;

new class extends Component
{
    //////////////////////////////////////////////////////////////////// PROPIEDADES PRINCIPALES
    //propiedades de titulos
    public string $title_book = 'Editar libros';
    public string $subtitle = 'Edite un libros de la lista';

    public string $title = '';
    public string $slug = '';
    public string $original_title = '';
    public string $synopsis = '';
    public int $release_date = 1;
    public float $number_collection = 1;
    public int $pages = 1;
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
    public $selectedBookSubjects = [];
    public $selectedBookCollections = [];
    public $selectedBookGenres = [];
    public $selectedBookTags = [];
    public $selectedBookLanguages = [];
    public $selectedFormats = [];

    // propiedades para lecturas
    public $start_read = null;
    public $end_read = null;
    
    // propiedades del item
    public $book;
    public $reads, $readId;

    //////////////////////////////////////////////////////////////////// VALIDACIONES
    // reglas de validacion
    protected function rules(){
        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', \Illuminate\Validation\Rule::unique('books', 'slug')->ignore($this->book?->id ?? 0)],
            'original_title' => ['nullable', 'string', 'max:255'],
            'synopsis' => ['nullable', 'string'],
            'release_date' => ['nullable', 'integer', 'min:1'],
            'number_collection' => ['required', 'numeric', 'min:0'],
            'pages' => ['nullable', 'integer', 'min:1'],
            'summary' => ['nullable', 'string'],
            'summary_clear' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
            'notes_clear' => ['nullable', 'string'],
            'is_favorite' => ['nullable', 'bool'],
            'is_abandonated' => ['nullable', 'bool'],
            'rating' => ['required', 'integer', 'min:0', 'max:5'],
            'cover_image_url' => ['nullable', 'url', 'max:65535'],
            'uuid' => ['required', 'string', 'max:255', \Illuminate\Validation\Rule::unique('books', 'uuid')->ignore($this->book?->id ?? 0)],
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
        'pages' => 'páginas',
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
    public function mount($bookUuid){
        // traer datos de libro
        $this->book = Book::where('user_id', \Illuminate\Support\Facades\Auth::id())
            ->with(['subjects', 'genres', 'collections', 'tags', 'reads', 'languages', 'readingFormats'])
            ->where('uuid', $bookUuid)->first();
        
        // poner datos a las propiedades
        $this->title = $this->book->title; 
        $this->slug = $this->book->slug; 
        $this->original_title = $this->book->original_title; 
        $this->synopsis = $this->book->synopsis; 
        $this->release_date = $this->book->release_date; 
        $this->number_collection = $this->book->number_collection ?? 1; 
        $this->pages = $this->book->pages ?? 1; 
        $this->summary = $this->book->summary ?? ''; 
        $this->summary_clear = $this->book->summary_clear ?? ''; 
        $this->notes = $this->book->notes ?? ''; 
        $this->notes_clear = $this->book->notes_clear ?? ''; 
        $this->is_favorite = $this->book->is_favorite ?? false; 
        $this->is_abandonated = $this->book->is_abandonated ?? false; 
        $this->rating = $this->book->rating ?? 0; 
        $this->cover_image_url = $this->book->cover_image_url; 
        $this->user_id = $this->book->user_id; 
        $this->uuid = $this->book->uuid; 

        // poner en arrays las asociaciones de m2m
        $this->selectedBookGenres = $this->book->genres->pluck('id')->toArray() ?? [];
        $this->selectedBookSubjects = $this->book->subjects->pluck('id')->toArray() ?? [];
        $this->selectedBookCollections = $this->book->collections->pluck('id')->toArray() ?? [];
        $this->selectedBookLanguages = $this->book->languages->pluck('id')->toArray() ?? [];
        $this->selectedFormats = $this->book->readingFormats->pluck('id')->toArray() ?? [];
        $this->selectedBookTags = $this->book->tags->pluck('name')->toArray() ?? [];
    }

    //////////////////////////////////////////////////////////////////// DATOS PARA ASOCIAR
    // traer datos de generos para asociar
    public function genres(){
        return BookGenre::where('user_id', \Illuminate\Support\Facades\Auth::id())
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

    // traer datos de generos para asociar
    public function languages(){
        return Language::where('user_id', \Illuminate\Support\Facades\Auth::id())
            ->orderBy('name', 'asc')
            ->get();
    }

    // traer datos de generos para asociar
    public function formats(){
        return ReadingFormat::where('user_id', \Illuminate\Support\Facades\Auth::id())
            ->orderBy('name', 'asc')
            ->get();
    }

    //////////////////////////////////////////////////////////////////// MODAL PARA CARGAR READS
    // abrir modal de nota
    public function modalRead(){
        $this->start_read = null;
        $this->end_read = null;
        $this->modal('add-read')->show();
    }

    // agregar lectura
    public function addRead(){

        $this->validate([
            'start_read' => ['required', 'date'],
            'end_read' => ['nullable', 'date'],
        ]);

        \App\Models\Page\BookRead::create([
            'user_id' => \Illuminate\Support\Facades\Auth::id(),
            'book_id' => $this->book->id,
            'start_read' => $this->start_read,
            'end_read' => $this->end_read,
        ]);

        $this->reads = \App\Models\Page\BookRead::where('book_id', $this->book->id)->get();
        $this->start_read = '';
        $this->end_read = '';

        $this->modal('add-read')->close();

        session()->flash('success', 'Lectura agregada');
    }

    // modal para borrar nota
    public function deleteRead($id){
        $this->readId = $id;
        $this->modal('delete-read')->show();
    }
    // borrar nota
    public function destroyRead(){
        \App\Models\Page\BookRead::find($this->readId)->delete();
        $this->modal('delete-read')->close();
        $this->reads = \App\Models\Page\BookRead::where('book_id', $this->book->id)->get();
        $this->readId = '';
        session()->flash('success', 'Lecura eliminada');
    }

    //////////////////////////////////////////////////////////////////// EDITAR DATOS
    // crear item en la BD
    public function updateItem(){
        // datos automaticos
        $this->slug = \Illuminate\Support\Str::slug($this->title . '-' . \Illuminate\Support\Str::random(4));
        $this->summary_clear = $this->cleanNotes($this->summary);
        $this->notes_clear = $this->cleanNotes($this->notes);

        // validar
        $validatedData = $this->validate();

        // crear en BD
        $this->book->update($validatedData);
        $this->book->subjects()->sync($this->selectedBookSubjects);
        $this->book->collections()->sync($this->selectedBookCollections);
        $this->book->genres()->sync($this->selectedBookGenres);
        $this->book->languages()->sync($this->selectedBookLanguages);
        $this->book->readingFormats()->sync($this->selectedFormats);

        // crear reads
        if($this->start_read || $this->end_read){
            \App\Models\Page\BookRead::create([
                'user_id' => \Illuminate\Support\Facades\Auth::id(),
                'book_id' => $this->book->id,
                'start_read' => $this->start_read,
                'end_read' => $this->end_read,
            ]);
        };

        // agregar tags
        $tagIds = [];
        foreach ($this->selectedBookTags as $tagName) {
            $tag = \App\Models\Page\Btag::firstOrCreate(
                ['name' => $tagName],
                [
                    'slug' => \Illuminate\Support\Str::slug($tagName),
                    'uuid' => \Illuminate\Support\Str::random(24),
                    'user_id' => \Illuminate\Support\Facades\Auth::id(),
                ]
            );

            $tagIds[] = $tag->id;
        }
        $this->book->tags()->sync($tagIds);

        session()->flash('success', 'Editado correctamente');

        // redireccionar
        $this->redirectRoute('books.index', navigate:true);
    }

    //////////////////////////////////////////////////////////////////// STORE PARA DATOS DE ASOCIACION ADICIONALES
    // store para crear una coleccion
    public $name_collection;
    public $books_amount_collection;
    public $movies_amount_collection;
    public $seasons_amount_collection;
    public function storeCollection(){
        $this->validate([
            'name_collection' => ['required', 'string', 'max:255'],
            'books_amount_collection' => ['nullable', 'numeric'],
            'movies_amount_collection' => ['nullable', 'numeric'],
            'seasons_amount_collection' => ['nullable', 'numeric'],
        ]);

        $s = Collection::create([
            'name' => trim($this->name_collection),
            'books_amount' => $this->books_amount_collection ?? 0,
            'movies_amount' => $this->movies_amount_collection ?? 0,
            'seasons_amount' => $this->seasons_amount_collection ?? 0,
            'slug' => \Illuminate\Support\Str::slug(trim($this->name_collection) . '-' . \Illuminate\Support\Str::random(4)),
            'uuid' => \Illuminate\Support\Str::random(24),
            'user_id' => \Illuminate\Support\Facades\Auth::id(),
        ]);

        $this->reset('name_collection', 'books_amount_collection', 'movies_amount_collection', 'seasons_amount_collection', 'selectedBookCollections');
        $this->collections();
        $this->selectedBookCollections[] = $s->id;
        $this->modal('add-collection')->close();
    }

    // store para crear un sujeto
    public $name_subject;
    public function storeSubject(){
        $this->validate([
            'name_subject' => ['required', 'string', 'max:255'],
        ]);

        // crear en BD
        $s = Subject::create([
            'name' => trim($this->name_subject),
            'slug' => \Illuminate\Support\Str::slug(trim($this->name_subject) . '-' . \Illuminate\Support\Str::random(4)),
            'uuid' => \Illuminate\Support\Str::random(24),
            'user_id' => \Illuminate\Support\Facades\Auth::id(),
        ]);

        $this->reset('name_subject', 'selectedBookSubjects');
        $this->collections();
        $this->selectedBookSubjects[] = $s->id;
        $this->modal('add-subject')->close();
    }

    //////////////////////////////////////////////////////////////////// STORE PARA TAGS
    // store para crear tags
    public $name_tag;
    public $newTag = '';   // input actual
    // public $selectedBookTags = [];     // array de tags agregados

    public function addTag()
    {
        $formatted = \Illuminate\Support\Str::of($this->newTag)
            ->lower()
            ->title()
            ->replace(' ', '');

        if ($formatted && !in_array($formatted, $this->selectedBookTags)) {
            $this->selectedBookTags[] = $formatted;
        }

        $this->newTag = '';
    }

    public function removeTag($index)
    {
        unset($this->selectedBookTags[$index]);
        $this->selectedBookTags = array_values($this->selectedBookTags); // reindexa
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
                <a href="{{ route('books.index') }}"><flux:button size="xs" variant="ghost" icon="arrow-uturn-left"></flux:button></a>
                {{ $this->title_book }}
            </flux:heading>
            <flux:text class="text-base">{{ $this->subtitle }}</flux:text>
    
            <flux:breadcrumbs>
                <flux:breadcrumbs.item href="{{ route('dashboard') }}">Dashboard</flux:breadcrumbs.item>
                <flux:breadcrumbs.item href="{{ route('books.index') }}">Libros</flux:breadcrumbs.item>
                <flux:breadcrumbs.item>{{ $this->title_book }}</flux:breadcrumbs.item>
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
            <flux:input type="number" max="2999" min="1" label="Año de publicacion" wire:model="release_date"/>
            <flux:input type="number" max="2999" min="1" label="Paginas" wire:model="pages"/>
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

        <flux:checkbox.group wire:model="selectedBookLanguages" label="Idiomas">
            <div class="grid grid-cols-3 gap-1">
                @foreach($this->languages() as $item)
                        <flux:checkbox label="{{ $item->name }}" value="{{ $item->id }}"/>
                @endforeach
            </div>
        </flux:checkbox.group>

        <flux:checkbox.group wire:model="selectedFormats" label="Formatos">
            <div class="grid grid-cols-3 gap-1">
                @foreach($this->formats() as $item)
                        <flux:checkbox label="{{ $item->name }}" value="{{ $item->id }}"/>
                @endforeach
            </div>
        </flux:checkbox.group>

        <div class="flex gap-2 items-center">

            <flux:button wire:click="modalRead" class="mt-1" size="sm" variant="ghost" color="purple" icon="plus" type="submit"></flux:button>
            <flux:separator text="Lecturas" />
            
        </div>

        @if ($book->reads)
            @foreach ($book->reads as $read)
            <div class="flex items-start justify-between">
                <div class="px-3 border-l-4 border-purple-800">
                    @if ($read->end_read)
                        <p class="mb-2 text-xs sm:text-base text-gray-800 dark:text-gray-300 ">{{ \Carbon\Carbon::parse($read->start_read)->format('Y-m-d') }} - {{ \Carbon\Carbon::parse($read->end_read)->format('Y-m-d') }} en {{ \Carbon\Carbon::parse($read->start_read)->diffInDays($read->end_read) }} dias</p>
                    @else
                        <p class="mb-2 text-xs sm:text-base text-gray-800 dark:text-gray-300 ">{{ \Carbon\Carbon::parse($read->start_read)->format('Y-m-d') }} - {{ \Carbon\Carbon::parse($read->end_read)->format('Y-m-d') }} Leyendo</p>
                    @endif

                </div>

                <flux:button wire:click="deleteRead({{ $read->id }})" class="ml-3 text-gray-400 hover:text-red-500 transition" size="sm" variant="ghost" color="purple" type="submit">✕</flux:button>
            </div>
            @endforeach
        @endif

        {{-- modales para agrear y eliminar lecturas --}}
        <flux:modal name="add-read" class="md:w-96">
            <div class="space-y-6">
                <div>
                    <flux:heading size="lg">Lectura</flux:heading>
                    <flux:text class="mt-2">Agregue una fecha de lectura.</flux:text>
                </div>
                <div class="grid grid-cols-2 gap-1">
                    <flux:input wire:model='start_read' type="date" max="2999-12-31" label="Inicio de lectura" />
                    <flux:input wire:model='end_read' type="date" max="2999-12-31" label="Fin de lectura" />
                </div>
                <div class="flex gap-2">
                    <flux:spacer />

                    <flux:modal.close>
                        <flux:button variant="ghost">Cancelar</flux:button>
                    </flux:modal.close>

                    <flux:button wire:click="addRead" type="submit" variant="primary">Editar</flux:button>
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

                    <flux:button wire:click="destroyRead" type="submit" variant="danger">Borrar</flux:button>
                </div>
            </div>
        </flux:modal>

        <flux:select wire:model="selectedBookGenres" label="Genero">
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
                <flux:select wire:model="selectedBookCollections">
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
                <flux:label>Autor {{ count($selectedBookSubjects) }}</flux:label>
            </flux:modal.trigger>
        </div>
        <flux:checkbox.group wire:model.live="selectedBookSubjects">
            <div class="h-40 overflow-scroll space-y-1">
                @foreach ($this->subjects() as $item)
                    <flux:checkbox label="{{ $item->name }}" value="{{ $item->id }}" />
                @endforeach
            </div>
        </flux:checkbox.group>

        <flux:input type="text" label="Etiquetas" wire:model="newTag" wire:keydown.space.prevent="addTag" placeholder="Agregue etiquetas" />
        <div class="flex gap-2 mt-2">
            @foreach($selectedBookTags as $index => $tag)
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

        <flux:button icon="pencil-square" wire:click="updateItem">Editar</flux:button>
    </div>

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
                    <flux:heading size="lg">Crear autor</flux:heading>
                    <flux:text class="mt-2">Cree un autor que no este en el listado.</flux:text>
                </div>

                <flux:input label="Nombre" placeholder="Nombre del autor" wire:model="name_subject" autofocus/>

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