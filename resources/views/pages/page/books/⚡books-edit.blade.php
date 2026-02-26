<?php

use App\Models\Page\Book;
use App\Models\Page\BookGenre;
use App\Models\Page\Collection;
use App\Models\Page\Subject;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component
{
    //propiedades de titulos
    public string $title_book = 'Editar libros';
    public string $subtitle = 'Edite un libros de la lista';

    // propiedades del item
    public $book;

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
    public $reads, $readId;

    // cargar datos del libro
    public function mount($bookUuid){
        $book = Book::where('user_id', \Illuminate\Support\Facades\Auth::id())
            ->with(['book_subjects', 'book_book_genres', 'book_collections', 'book_reads'])
            ->where('uuid', $bookUuid)->first();
        $this->book = $book;
        
        $this->title = $book->title; 
        $this->slug = $book->slug; 
        $this->original_title = $book->original_title; 
        $this->synopsis = $book->synopsis; 
        $this->release_date = $book->release_date; 
        $this->number_collection = $book->number_collection; 
        $this->pages = $book->pages; 
        $this->summary = $book->summary; 
        $this->summary_clear = $book->summary_clear; 
        $this->notes = $book->notes; 
        $this->notes_clear = $book->notes_clear; 
        $this->is_favorite = $book->is_favorite; 
        $this->is_abandonated = $book->is_abandonated; 
        $this->rating = $book->rating; 
        $this->cover_image_url = $book->cover_image_url; 
        $this->user_id = $book->user_id; 
        $this->uuid = $book->uuid; 

        $this->selected_book_book_genres = $book->book_book_genres->pluck('id')->toArray() ?? [];
        $this->selected_book_subjects = $book->book_subjects->pluck('id')->toArray() ?? [];
        $this->selected_book_collections = $book->book_collections->pluck('id')->toArray() ?? [];
    }

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
        // \Flux\Flux::modal('delete-quote')->show();

    }
    // borrar nota
    public function destroyRead(){
        
        \App\Models\Page\BookRead::find($this->readId)->delete();

        $this->modal('delete-read')->close();
        // \Flux\Flux::modal('delete-quote')->close();

        $this->reads = \App\Models\Page\BookRead::where('book_id', $this->book->id)->get();
        $this->readId = '';

        session()->flash('success', 'Lecura eliminada');
    }

    // crear item en la BD
    public function updateItem(){
        // datos automaticos
        $this->slug = \Illuminate\Support\Str::slug($this->title . '-' . \Illuminate\Support\Str::random(4));

        // validar
        $validatedData = $this->validate();

        // crear en BD
        $this->book->update($validatedData);
        $this->book->book_subjects()->sync($this->selected_book_subjects);
        $this->book->book_collections()->sync($this->selected_book_collections);
        $this->book->book_book_genres()->sync($this->selected_book_book_genres);

        if($this->start_read || $this->end_read){
            \App\Models\Page\BookRead::create([
                'user_id' => \Illuminate\Support\Facades\Auth::id(),
                'book_id' => $this->book->id,
                'start_read' => $this->start_read,
                'end_read' => $this->end_read,
            ]);
        };

        session()->flash('success', 'Editado correctamente');

        // redireccionar
        $this->redirectRoute('books.index', navigate:true);
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

            <div class="flex gap-2 items-center">

                <flux:button wire:click="modalRead" class="mt-1" size="sm" variant="ghost" color="purple" icon="plus" type="submit"></flux:button>
                <flux:separator text="Lecturas" />
                
            </div>

            @if ($book->book_reads)
                @foreach ($book->book_reads as $read)
                <div class="flex items-start justify-between">
                    <div class="px-3 border-l-4 border-purple-800">
                        @if ($read->end_read)
                            <p class="mb-2 text-xs sm:text-base text-gray-800 dark:text-gray-300 ">{{ $read->start_read }} - {{ $read->end_read }} en {{ \Carbon\Carbon::parse($read->start_read)->diffInDays($read->end_read) }} dias</p>
                        @else
                            <p class="mb-2 text-xs sm:text-base text-gray-800 dark:text-gray-300 ">{{ $read->start_read }} - {{ $read->end_read }} Leyendo</p>
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

            {{-- <flux:textarea wire:model='quoteContent' row="20" label="Cita o Frase" placeholder="Coloque la la cita o frase" resize="vertical"/> --}}
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

        <flux:select wire:model="selected_book_book_genres" label="Genero">
            <option value="">Seleccionar genero</option>
            @foreach ($this->book_genres() as $item)
                <option value="{{ $item->id }}">{{ $item->name_general }} - {{ $item->name }}</option>
            @endforeach
        </flux:select>

        <flux:select wire:model="selected_book_collections" label="Saga">
            <option value="">Seleccionar saga</option>
            @foreach ($this->book_collections() as $item)
                <option value="{{ $item->id }}">{{ $item->name }}</option>
            @endforeach
        </flux:select>

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

        <flux:button icon="pencil-square" wire:click="updateItem">Editar</flux:button>
    </div>
</div>