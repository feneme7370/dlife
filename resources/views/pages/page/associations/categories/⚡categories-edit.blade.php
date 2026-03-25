<?php

use App\Models\Page\Category;
use Livewire\Component;

new class extends Component
{
    //////////////////////////////////////////////////////////////////// PROPIEDADES
    //propiedades de titulos
    public string $titlePage = '';
    public string $subtitlePage = '';
    public string $buttonSubmit = '';
    
    // propiedades del item
    public $category;

    // propiedades del formulario
    public ?string $name = null;
    public ?string $slug = null;
    public ?string $category_type = null;
    public ?string $description = null;
    public ?string $cover_image_url = null;
    public ?string $uuid = null;
    public ?int $user_id = null;

    //////////////////////////////////////////////////////////////////// PRE CARGAR DATOS
    // precargar datos al iniciar pagina
    public function mount($categoryUuid = null){
        $this->category = Category::where('uuid', $categoryUuid)->first();

        $this->titlePage = $this->category ? 'Modificar categoria' : 'Agregar categoria';
        $this->subtitlePage = $this->category ? 'Modificar datos de la categoria' : 'Agregar datos de la categoria';
        $this->buttonSubmit = $this->category ? 'Modificar' : 'Agregar';

        $this->name = $this->category?->name ?? null;
        $this->slug = $this->category?->slug ?? null;
        $this->category_type = $this->category?->category_type ?? null;
        $this->description = $this->category?->description ?? null;
        $this->cover_image_url = $this->category?->cover_image_url ?? null;
        $this->uuid = $this->category?->uuid ?? null;
        $this->user_id = $this->category?->user_id ?? \Illuminate\Support\Facades\Auth::id();

    }

    //////////////////////////////////////////////////////////////////// VALIDACIONES
    // reglas de validacion
    protected function rules(){
        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', \Illuminate\Validation\Rule::unique('categories', 'slug')->ignore($this->category?->id ?? 0)],
            'category_type' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'cover_image_url' => ['nullable', 'url', 'max:65535'],
            'uuid' => ['required', 'string', 'max:255', \Illuminate\Validation\Rule::unique('categories', 'uuid')->ignore($this->category?->id ?? 0)],
            'user_id' => ['required', 'exists:users,id'],
        ];
    }

    // renombrar variables a castellano
    protected $validationAttributes = [
        'name' => 'nombre',
        'slug' => 'nombre url',
        'category_type' => 'tipo de categoria',
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

        if($this->category){
            // validar
            $validatedData = $this->validate();

            // actualizar item en BD
            $this->category->update($validatedData);

            // mensaje de success
            session()->flash('success', 'Editado correctamente');

        }else{
            // datos automaticos
            $this->user_id = \Illuminate\Support\Facades\Auth::id();
            $this->uuid = \Illuminate\Support\Str::random(24);

            // validar
            $validatedData = $this->validate();

            // crear en BD
            Category::create($validatedData);
            
            // mensaje de success
            session()->flash('success', 'Creado correctamente');
        }

        // redireccionar
        $this->redirectRoute('categories.index', navigate:true);
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
            ['label' => 'Categorias', 'route' => 'categories.index'],
            ['label' => $this->titlePage]
        ]"
    />

    {{-- formulario completo --}}
    <div class="space-y-2">
        <flux:input type="text" label="Nombre" wire:model="name" placeholder="Nombre del genero" autofocus/>

        <flux:radio.group wire:model="category_type" label="Seleccion a donde pertenece el genero">
            <flux:radio value="diaries" label="Diarios" />
            <flux:radio value="recipes" label="Recetas" checked />
        </flux:radio.group>

        <flux:input type="text" label="Link de imagen" wire:model="cover_image_url" placeholder="Pegue el link de una imagen"/>
        
        <flux:textarea
            label="Descripcion"
            placeholder="Coloque una descripcion de la categoria"
            wire:model="description"
            rows="5"
        />

        <x-libraries.utilities.errors />

        <flux:button :icon="$category ? 'pencil-square' : 'plus'" wire:click="updateItem">{{ $this->buttonSubmit }}</flux:button>
    </div>
</div>