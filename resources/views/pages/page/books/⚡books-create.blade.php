<?php

use App\Models\Page\Book;
use App\Models\Page\BookGenre;
use App\Models\Page\Collection;
use App\Models\Page\Subject;
use Livewire\Component;

new class extends Component
{
    //propiedades de titulos
    public string $title_book = 'Agregar libros';
    public string $subtitle = 'Agregue un libros a la lista';

    // propiedades del item
    public string $title = '';
    public string $slug = '';
    public string $original_title = '';
    public string $synopsis = '';
    public int $release_date = 1;
    public int $number_collection = 1;
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
    public $selected_book_subjects = [];
    public $selected_book_collections = [];
    public $selected_book_book_genres = [];

    public $start_read = null;
    public $end_read = null;

    // reglas de validacion
    protected function rules(){
        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', \Illuminate\Validation\Rule::unique('books', 'slug')->ignore($this->book?->id ?? 0)],
            'original_title' => ['nullable', 'string', 'max:255'],
            'synopsis' => ['nullable', 'string'],
            'release_date' => ['nullable', 'integer', 'min:1'],
            'number_collection' => ['required', 'integer', 'min:1'],
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

    // traer datos de generos para asociar
    public function book_genres(){
        return BookGenre::where('user_id', \Illuminate\Support\Facades\Auth::id())
            ->orderBy('name_general', 'asc')
            ->get();
    }

    // traer datos de colecciones para asociar
    public function book_collections(){
        return Collection::where('user_id', \Illuminate\Support\Facades\Auth::id())
            ->orderBy('name', 'asc')
            ->get();
    }

    // traer datos de generos para asociar
    public function book_subjects(){
        return Subject::where('user_id', \Illuminate\Support\Facades\Auth::id())
            ->orderBy('name', 'asc')
            ->get();
    }

    // crear item en la BD
    public function storeItem(){
        // datos automaticos
        $this->user_id = \Illuminate\Support\Facades\Auth::id();
        $this->slug = \Illuminate\Support\Str::slug($this->title . '-' . \Illuminate\Support\Str::random(4));
        $this->uuid = \Illuminate\Support\Str::random(24);

        // validar
        $validatedData = $this->validate();

        // crear en BD
        $book = Book::create($validatedData);
        $book->book_subjects()->sync($this->selected_book_subjects);
        $book->book_collections()->sync($this->selected_book_collections);
        $book->book_book_genres()->sync($this->selected_book_book_genres);

        if($this->start_read || $this->end_read){
            \App\Models\Page\BookRead::create([
                'user_id' => \Illuminate\Support\Facades\Auth::id(),
                'book_id' => $book->id,
                'start_read' => $this->start_read,
                'end_read' => $this->end_read,
            ]);
        };

        session()->flash('success', 'Creado correctamente');

        // redireccionar
        $this->redirectRoute('books.index', navigate:true);
    }

    // store para crear una coleccion
    public $name_collection;
    public $books_amount_collection;
    public $movies_amount_collection;
    public function storeCollection(){
        $this->validate([
            'name_collection' => ['required', 'string', 'max:255'],
            'books_amount_collection' => ['nullable', 'numeric'],
            'movies_amount_collection' => ['nullable', 'numeric'],
        ]);

        // crear en BD
        Collection::create([
            'name' => trim($this->name_collection),
            'books_amount' => $this->books_amount_collection ?? 0,
            'movies_amount' => $this->movies_amount_collection ?? 0,
            'slug' => \Illuminate\Support\Str::slug(trim($this->name_collection) . '-' . \Illuminate\Support\Str::random(4)),
            'uuid' => \Illuminate\Support\Str::random(24),
            'user_id' => \Illuminate\Support\Facades\Auth::id(),
        ]);

        $this->reset('name_collection', 'books_amount_collection', 'movies_amount_collection', 'selected_book_collections');
        $this->book_collections();
        $this->modal('add-collection')->close();
    }

    // store para crear un sujeto
    public $name_subject;
    public function storeSubject(){
        $this->validate([
            'name_subject' => ['required', 'string', 'max:255'],
        ]);

        // crear en BD
        Subject::create([
            'name' => trim($this->name_subject),
            'slug' => \Illuminate\Support\Str::slug(trim($this->name_subject) . '-' . \Illuminate\Support\Str::random(4)),
            'uuid' => \Illuminate\Support\Str::random(24),
            'user_id' => \Illuminate\Support\Facades\Auth::id(),
        ]);

        $this->reset('name_subject', 'selected_book_subjects');
        $this->book_collections();
        $this->modal('add-subject')->close();
    }
};
?>

<div>
    {{-- titulo, descripcion y breadcrumbs --}}
    <div>
        <flux:main class="mb-1 space-y-1">
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
        </flux:main>
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
            <flux:input type="number" max="2999" min="1" label="N° de coleccion" wire:model="number_collection"/>
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
            <flux:input wire:model='start_read' type="date" max="2999-12-31" label="Inicio de lectura" />
            <flux:input wire:model='end_read' type="date" max="2999-12-31" label="Fin de lectura" />
        </div>

        <flux:select wire:model="selected_book_book_genres" label="Genero">
            <option value="">Seleccionar genero</option>
            @foreach ($this->book_genres() as $item)
                <option value="{{ $item->id }}">{{ $item->name_general }} - {{ $item->name }}</option>
            @endforeach
        </flux:select>

        <div class="flex items-center gap-1">
            <flux:modal.trigger name="add-collection">
                <flux:button size="xs" variant="ghost" icon="plus"></flux:button>
            </flux:modal.trigger>
            <flux:label>Saga</flux:label>
        </div>
        <flux:select wire:model="selected_book_collections">
            <option value="">Seleccionar saga</option>
            @foreach ($this->book_collections() as $item)
                <option value="{{ $item->id }}">{{ $item->name }}</option>
            @endforeach
        </flux:select>

        <div class="flex items-center gap-1">
            <flux:modal.trigger name="add-subject">
                <flux:button size="xs" variant="ghost" icon="plus"></flux:button>
                <flux:label>Autor</flux:label>
            </flux:modal.trigger>
        </div>

        <flux:checkbox.group wire:model.live="selected_book_subjects" :label="'Autor(es) '.count($selected_book_subjects)">
            <div class="h-40 overflow-scroll space-y-1">
                @foreach ($this->book_subjects() as $item)
                    <flux:checkbox label="{{ $item->name }}" value="{{ $item->id }}" />
                @endforeach
            </div>
        </flux:checkbox.group>

        <flux:textarea
            label="Resumen General 🗒️"
            placeholder="Escriba lo que recuerde del libro"
            wire:model="summary_clear"
            rows="6"
        />
        <flux:textarea
            label="Reseña propia ✍️"
            placeholder="Escriba su reseña"
            wire:model="notes_clear"
            rows="6"
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
                <flux:input type="number" label="Numero de peliculas" placeholder="Cantidad de peliculas" wire:model="movies_amount_collection"/>

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