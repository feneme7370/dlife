<?php

use App\Models\Page\Genre;
use Livewire\Component;

new class extends Component
{
    //////////////////////////////////////////////////////////////////// PROPIEDADES
    //propiedades de titulos
    public string $titlePage = '';
    public string $subtitlePage = '';
    public string $buttonSubmit = '';
    
    // propiedades del item
    public $genre;

    // propiedades del formulario
    public ?string $name = null;
    public ?string $slug = null;
    public ?string $genre_type = null;
    public ?string $description = null;
    public ?string $cover_image_url = null;
    public ?string $uuid = null;
    public ?int $user_id = null;

    //////////////////////////////////////////////////////////////////// PRE CARGAR DATOS
    // precargar datos al iniciar pagina
    public function mount($genreUuid = null){
        $this->genre = Genre::where('uuid', $genreUuid)->first();

        $this->titlePage = $this->genre ? 'Modificar sujeto' : 'Agregar sujeto';
        $this->subtitlePage = $this->genre ? 'Modificar datos del sujeto' : 'Agregar datos del sujeto';
        $this->buttonSubmit = $this->genre ? 'Modificar' : 'Agregar';

        $this->name = $this->genre?->name ?? null;
        $this->slug = $this->genre?->slug ?? null;
        $this->genre_type = $this->genre?->genre_type ?? null;
        $this->description = $this->genre?->description ?? null;
        $this->cover_image_url = $this->genre?->cover_image_url ?? null;
        $this->uuid = $this->genre?->uuid ?? null;
        $this->user_id = $this->genre?->user_id ?? \Illuminate\Support\Facades\Auth::id();
    }

    //////////////////////////////////////////////////////////////////// VALIDACIONES
    // reglas de validacion
    protected function rules(){
        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', \Illuminate\Validation\Rule::unique('genres', 'slug')->ignore($this->genre?->id ?? 0)],
            'genre_type' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'cover_image_url' => ['nullable', 'url', 'max:65535'],
            'uuid' => ['required', 'string', 'max:255', \Illuminate\Validation\Rule::unique('genres', 'uuid')->ignore($this->genre?->id ?? 0)],
            'user_id' => ['required', 'exists:users,id'],
        ];
    }

    // renombrar variables a castellano
    protected $validationAttributes = [
        'name' => 'nombre',
        'slug' => 'nombre url',
        'genre_type' => 'tipo de genero',
        'description' => 'descripcion',
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

        if($this->genre){
            // validar
            $validatedData = $this->validate();

            // actualizar item en BD
            $this->genre->update($validatedData);

            // mensaje de success
            session()->flash('success', 'Editado correctamente');

        }else{
            // datos automaticos
            $this->user_id = \Illuminate\Support\Facades\Auth::id();
            $this->uuid = \Illuminate\Support\Str::random(24);

            // validar
            $validatedData = $this->validate();

            // crear en BD
            Genre::create($validatedData);
            
            // mensaje de success
            session()->flash('success', 'Creado correctamente');
        }

        // redireccionar
        $this->redirectRoute('genres.index', navigate:true);
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
            ['label' => 'Generos', 'route' => 'genres.index'],
            ['label' => $this->titlePage]
        ]"
    />

    {{-- formulario completo --}}
    <div class="space-y-2">
        <flux:input type="text" label="Nombre" wire:model="name" placeholder="Nombre del genero" autofocus/>

        <flux:radio.group wire:model="genre_type" label="Seleccion a donde pertenece el genero">
            <flux:radio value="books" label="Libros" checked />
            <flux:radio value="visual" label="Peliculas y Series" />
        </flux:radio.group>

        <flux:input type="text" label="Link de imagen" wire:model="cover_image_url" placeholder="Pegue el link de una imagen"/>
        
        <flux:textarea
            label="Descripcion"
            placeholder="Coloque una descripcion del genero"
            wire:model="description"
            rows="5"
        />

        <x-libraries.utilities.errors />

        <flux:button :icon="$genre ? 'pencil-square' : 'plus'" wire:click="updateItem">{{ $this->buttonSubmit }}</flux:button>
    </div>
</div>