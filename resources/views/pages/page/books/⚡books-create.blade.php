<?php

use App\Models\Page\Book;
use App\Models\Page\BookGenre;
use App\Models\Page\Collection;
use App\Models\Page\Language;
use App\Models\Page\ReadingFormat;
use App\Models\Page\Subject;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

new class extends Component
{
    use \App\Traits\HandlesTags;
    use \App\Traits\CleansHtml;
    use \App\Traits\WithCollections;
    use \App\Traits\WithSubjects;

    //////////////////////////////////////////////////////////////////// PROPIEDADES PRINCIPALES
    //propiedades de titulos
    public string $titlePage = 'Agregar libros';
    public string $subtitlePage = 'Agregue un libros a la lista';

    // propiedades del item
    public string $title = '';
    public string $slug = '';
    public string $original_title = '';
    public string $synopsis = '';
    public int $release_date = 1;
    public float $number_collection = 1;
    public int $pages = 1;
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
    public $selectedBookSubjects = [];
    public $selectedBookCollections = [];
    public $selectedBookTags = [];
    public $selectedBookGenres = [];
    public $selectedBookLanguages = [1];
    public $selectedBookReadingFormats = [2];

    // propiedades para lecturas
    public $start_read = null;
    public $end_read = null;

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
            'type' => ['nullable', 'integer', 'min:1'],
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
        return BookGenre::where('user_id', \Illuminate\Support\Facades\Auth::id())->orderBy('name_general', 'asc')->get();
    }

    // traer datos de colecciones para asociar
    public function collections(){
        return Collection::where('user_id', \Illuminate\Support\Facades\Auth::id())->orderBy('name', 'asc')->get();
    }

    // traer datos de generos para asociar
    public function subjects(){
        return Subject::where('user_id', \Illuminate\Support\Facades\Auth::id())->orderBy('name', 'asc')->get();
    }

    // traer datos de generos para asociar
    public function languages(){
        return Language::where('user_id', \Illuminate\Support\Facades\Auth::id())->orderBy('name', 'asc')->get();
    }

    // traer datos de generos para asociar
    public function formats(){
        return ReadingFormat::where('user_id', \Illuminate\Support\Facades\Auth::id())->orderBy('name', 'asc')->get();
    }

    // traer tipos
    public function types(){return Book::type();}

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

        // validar
        $validatedData = $this->validate();

        // crear en BD
        $book = Book::create($validatedData);
        $book->subjects()->sync($this->selectedBookSubjects);
        $book->collections()->sync($this->selectedBookCollections);
        $book->genres()->sync($this->selectedBookGenres);
        $book->languages()->sync($this->selectedBookLanguages);
        $book->readingFormats()->sync($this->selectedBookReadingFormats);

        // agregar read de libro
        if($this->start_read || $this->end_read){
            \App\Models\Page\BookRead::create([
                'user_id' => \Illuminate\Support\Facades\Auth::id(),
                'book_id' => $book->id,
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
        $book->tags()->sync($tagIds);

        session()->flash('success', 'Creado correctamente');

        // redireccionar
        $this->redirectRoute('books.index', navigate:true);
    }
};
?>

<div>
    {{-- titulo, descripcion y breadcrumbs --}}
    <div>
        <div class="mb-1 space-y-1">
            <flux:heading size="xl" level="1">
                <a href="{{ route('books.index') }}">
                    <flux:button size="xs" variant="ghost" icon="arrow-uturn-left"></flux:button>
                </a>
                {{ $this->titlePage }}
            </flux:heading>
            <flux:text class="text-base">{{ $this->subtitlePage }}</flux:text>

            <flux:breadcrumbs>
                <flux:breadcrumbs.item href="{{ route('dashboard') }}">Dashboard</flux:breadcrumbs.item>
                <flux:breadcrumbs.item href="{{ route('books.index') }}">Libros</flux:breadcrumbs.item>
                <flux:breadcrumbs.item>{{ $this->titlePage }}</flux:breadcrumbs.item>
            </flux:breadcrumbs>

            <flux:separator variant="subtle" />
        </div>
    </div>

    {{-- formulario completo --}}
    <div class="space-y-2">

        <flux:input type="text" label="Nombre" wire:model="title" placeholder="Nombre del libro" autofocus />
        <flux:input type="text" label="Nombre Original" wire:model="original_title"
            placeholder="Nombre del libro original" />
        <flux:textarea label="Sinopsis" placeholder="Coloque la sinopsis" wire:model="synopsis" rows="6" />
        <div class="grid grid-cols-3 gap-1">
            <flux:input type="number" max="2999" min="1" label="Año de publicacion" wire:model="release_date" />
            <flux:input type="number" max="2999" min="1" label="Paginas" wire:model="pages" />
            <flux:select wire:model="type" label="Tipo">
                <option value="">Seleccionar tipo</option>
                @foreach ($this->types() as $key => $item)
                <option value="{{ $key }}">{{ $item }}</option>
                @endforeach
            </flux:select>
        </div>

        <flux:input type="text" label="Link de portada" wire:model="cover_image_url"
            placeholder="Pegue el link de una imagen" />

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
                <flux:checkbox label="{{ $item->name }}" value="{{ $item->id }}" />
                @endforeach
            </div>
        </flux:checkbox.group>

        <flux:checkbox.group wire:model="selectedBookReadingFormats" label="Formatos">
            <div class="grid grid-cols-3 gap-1">
                @foreach($this->formats() as $item)
                <flux:checkbox label="{{ $item->name }}" value="{{ $item->id }}" />
                @endforeach
            </div>
        </flux:checkbox.group>

        <div class="grid grid-cols-2 gap-1">
            <flux:input wire:model='start_read' type="date" max="2999-12-31" label="Inicio de lectura" />
            <flux:input wire:model='end_read' type="date" max="2999-12-31" label="Fin de lectura" />
        </div>

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
                <flux:input type="number" max="2999" min="0" step="0.1" wire:model="number_collection" />
            </div>
        </div>

        <div class="flex items-center gap-1">
            <flux:modal.trigger name="add-subject">
                <flux:button size="xs" variant="ghost" icon="plus"></flux:button>
                <flux:label>Autor(es) {{ count($selectedBookSubjects) }}</flux:label>
            </flux:modal.trigger>
        </div>
 
        <div class="col-span-12 sm:col-span-6">
            <x-libraries.flux.select-multiple
                model="subject" 
                relation="subjects" 
                wire:model="selectedBookSubjects" 
                {{-- label="Autores" --}}
                :items="$this->subjects()"
            />
        </div>

        <flux:label>Etiquetas</flux:label>
        <flux:input.group>
            <flux:input type="text" wire:model="newTag" wire:keydown.period.prevent="addTag('selectedBookTags')"
                placeholder="Agregue etiquetas" />
            <flux:button wire:click="addTag('selectedBookTags')" icon="plus">Agregar</flux:button>
        </flux:input.group>

        <div class="flex gap-2 mt-2">
            @foreach($selectedBookTags as $index => $tag)
            <flux:badge size="sm" color="purple">
                <button class="mr-2" wire:click="removeTag('selectedBookTags', {{ $index }})">
                    x
                </button>
                #{{ $tag }}
            </flux:badge>
            @endforeach
        </div>

        <x-libraries.quill-textarea-form id_quill="editor_create_summary" name="summary" rows="15"
            placeholder="{{ __('Resumen personal') }}" model="summary" model_data="{{ $summary }}" />

        <x-libraries.quill-textarea-form id_quill="editor_create_notes" name="notes" rows="15"
            placeholder="{{ __('Reseña') }}" model="notes" model_data="{{ $notes }}" />

        <x-libraries.utilities.errors />

        <flux:button icon="plus" wire:click="storeItem">Agregar</flux:button>
    </div>

    {{-- modal para agregar coleccion --}}
    <flux:modal name="add-collection" class="md:w-96">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Crear saga</flux:heading>
                <flux:text class="mt-2">Cree una saga que no este en el listado.</flux:text>
            </div>

            <flux:input label="Nombre" placeholder="Nombre de la saga" wire:model="name_collection" autofocus />
            <flux:input type="number" label="Numero de libros" placeholder="Cantidad de libros"
                wire:model="books_amount_collection" />
            <flux:input type="number" label="Numero de peliculas" placeholder="Cantidad de peliculas"
                wire:model="movies_amount_collection" />
            <flux:input type="number" label="Numero de temporadas" placeholder="Cantidad de temporadas"
                wire:model="seasons_amount_collection" />

            <div class="flex">
                <flux:spacer />

                <x-libraries.utilities.errors />

                <flux:button wire:click="storeCollection('selectedBookCollections')" variant="primary">Agregar
                </flux:button>
            </div>
        </div>
    </flux:modal>

    {{-- modal para agregar sujeto --}}
    <flux:modal name="add-subject" class="md:w-96">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Crear autor</flux:heading>
                <flux:text class="mt-2">Cree un autor que no este en el listado.</flux:text>
            </div>

            <flux:input label="Nombre" placeholder="Nombre del autor" wire:model="name_subject" autofocus />

            <div class="flex">
                <flux:spacer />

                <x-libraries.utilities.errors />

                <flux:button wire:click="storeSubject('selectedBookSubjects')" variant="primary">Agregar</flux:button>
            </div>
        </div>
    </flux:modal>

</div>