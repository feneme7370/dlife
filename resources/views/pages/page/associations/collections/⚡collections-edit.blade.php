<?php

use App\Models\Page\Collection;
use Livewire\Component;

new class extends Component
{
    //////////////////////////////////////////////////////////////////// PROPIEDADES
    //propiedades de titulos
    public string $titlePage = '';
    public string $subtitlePage = '';
    public string $buttonSubmit = '';
    
    // propiedades del item
    public $collection;

    // propiedades del formulario
    public ?string $name = null;
    public ?string $slug = null;
    public ?string $description = null;
    public int $books_amount = 0;
    public int $movies_amount = 0;
    public int $seasons_amount = 0;
    public ?string $cover_image_url = null;
    public ?string $uuid = null;
    public ?int $user_id = null;

    //////////////////////////////////////////////////////////////////// PRE CARGAR DATOS
    // precargar datos al iniciar pagina
    public function mount($collectionUuid = null){
        $this->collection = Collection::where('uuid', $collectionUuid)->first();

        $this->titlePage = $this->collection ? 'Modificar coleccion' : 'Agregar coleccion';
        $this->subtitlePage = $this->collection ? 'Modificar datos del coleccion' : 'Agregar datos del coleccion';
        $this->buttonSubmit = $this->collection ? 'Modificar' : 'Agregar';

        $this->name = $this->collection?->name ?? null;
        $this->slug = $this->collection?->slug ?? null;
        $this->description = $this->collection?->description ?? null;
        $this->books_amount = $this->collection?->books_amount ?? 0;
        $this->movies_amount = $this->collection?->movies_amount ?? 0;
        $this->seasons_amount = $this->collection?->seasons_amount ?? 0;
        $this->cover_image_url = $this->collection?->cover_image_url ?? null;
        $this->uuid = $this->collection?->uuid ?? null;
        $this->user_id = $this->collection?->user_id ?? \Illuminate\Support\Facades\Auth::id();
    }

    //////////////////////////////////////////////////////////////////// VALIDACIONES
    // reglas de validacion
    protected function rules(){
        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', \Illuminate\Validation\Rule::unique('collections', 'slug')->ignore($this->collection?->id ?? 0)],
            'description' => ['nullable', 'string'],
            'books_amount' => ['required', 'numeric'],
            'movies_amount' => ['required', 'numeric'],
            'seasons_amount' => ['required', 'numeric'],
            'cover_image_url' => ['nullable', 'url', 'max:65535'],
            'uuid' => ['required', 'string', 'max:255', \Illuminate\Validation\Rule::unique('collections', 'uuid')->ignore($this->collection?->id ?? 0)],
            'user_id' => ['required', 'exists:users,id'],
        ];
    }

    // renombrar variables a castellano
    protected $validationAttributes = [
        'name' => 'nombre',
        'slug' => 'nombre url',
        'description' => 'descripcion',
        'books_amount' => 'total de libros',
        'movies_amount' => 'total de peliculas',
        'seasons_amount' => 'total de temporadas',
        'cover_image_url' => 'imagen web',
        'uuid' => 'uuid',
        'user_id' => 'usuario',
    ];

    //////////////////////////////////////////////////////////////////// STORE PARA CREAR O EDITAR
    // editar o crear item en la BD
    public function updateItem(){
        // normalizar
        $this->name = \Illuminate\Support\Str::title(trim($this->name));
        $this->slug = \Illuminate\Support\Str::slug($this->name . '-' . \Illuminate\Support\Facades\Auth::id());

        if($this->collection){
            // validar
            $validatedData = $this->validate();

            // actualizar item en BD
            $this->collection->update($validatedData);

            // mensaje de success
            session()->flash('success', 'Editado correctamente');

        }else{
            // datos automaticos
            $this->user_id = \Illuminate\Support\Facades\Auth::id();
            $this->uuid = \Illuminate\Support\Str::random(24);

            // validar
            $validatedData = $this->validate();

            // crear en BD
            Collection::create($validatedData);
            
            // mensaje de success
            session()->flash('success', 'Creado correctamente');
        }

        // redireccionar
        $this->redirectRoute('collections.index', navigate:true);
    }
};
?>

<div>
    {{-- titulo, descripcion y breadcrumbs --}}
    <x-page.partials.title-page 
        :title="$this->titlePage"
        :create-route="'categories.index'"
        icon="arrow-uturn-left"
        :breadcrumbs="[
            ['label' => 'Dashboard', 'route' => 'dashboard'],
            ['label' => 'Asociaciones', 'route' => 'associations.index'],
            ['label' => 'Colecciones', 'route' => 'collections.index'],
            ['label' => $this->titlePage]
        ]"
    />

    {{-- formulario completo --}}
    <div class="space-y-2">
        <flux:input type="text" label="Nombre" wire:model="name" placeholder="Nombre del sujeto" autofocus/>
        <flux:input type="text" label="Link de imagen" wire:model="cover_image_url" placeholder="Pegue el link de una imagen"/>

        <div class="grid grid-cols-3 gap-1">
            <flux:input type="number" label="Total de libros" wire:model="books_amount" placeholder="Total de libros"/>
            <flux:input type="number" label="Total de peliculas" wire:model="movies_amount" placeholder="Total de peliculas"/>
            <flux:input type="number" label="Total de temporadas" wire:model="seasons_amount" placeholder="Total de temporadas"/>
        </div>
        
        <flux:textarea
            label="Descripcion"
            placeholder="Coloque una descripcion del sujeto"
            wire:model="description"
            rows="5"
        />

        <x-libraries.utilities.errors />

        <flux:button :icon="$collection ? 'pencil-square' : 'plus'" wire:click="updateItem">{{ $this->buttonSubmit }}</flux:button>
    </div>
</div>