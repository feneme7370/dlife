<?php

use App\Models\Page\Tag;
use Livewire\Component;

new class extends Component
{
    //////////////////////////////////////////////////////////////////// PROPIEDADES
    //propiedades de titulos
    public string $titlePage = '';
    public string $subtitlePage = '';
    public string $buttonSubmit = '';
    
    // propiedades del item
    public $tag;

    // propiedades del formulario
    public ?string $name = null;
    public ?string $slug = null;
    public ?string $tag_type = null;
    public ?string $uuid = null;
    public ?int $user_id = null;

    //////////////////////////////////////////////////////////////////// PRE CARGAR DATOS
    // precargar datos al iniciar pagina
    public function mount($tagUuid = null){
        $this->tag = Tag::where('uuid', $tagUuid)->first();

        $this->titlePage = $this->tag ? 'Modificar etiqueta' : 'Agregar etiqueta';
        $this->subtitlePage = $this->tag ? 'Modificar datos del etiqueta' : 'Agregar datos del etiqueta';
        $this->buttonSubmit = $this->tag ? 'Modificar' : 'Agregar';

        $this->name = $this->tag?->name ?? null;
        $this->slug = $this->tag?->slug ?? null;
        $this->tag_type = $this->tag?->tag_type ?? null;
        $this->uuid = $this->tag?->uuid ?? null;
        $this->user_id = $this->tag?->user_id ?? \Illuminate\Support\Facades\Auth::id();
    }

    //////////////////////////////////////////////////////////////////// VALIDACIONES
    // reglas de validacion
    protected function rules(){
        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', \Illuminate\Validation\Rule::unique('tags', 'slug')->ignore($this->tag?->id ?? 0)],
            'tag_type' => ['required', 'string'],
            'uuid' => ['required', 'string', 'max:255', \Illuminate\Validation\Rule::unique('tags', 'uuid')->ignore($this->tag?->id ?? 0)],
            'user_id' => ['required', 'exists:users,id'],
        ];
    }

    // renombrar variables a castellano
    protected $validationAttributes = [
        'name' => 'nombre',
        'slug' => 'nombre url',
        'tag_type' => 'tipo de etiqueta',
        'uuid' => 'uuid',
        'user_id' => 'usuario',
    ];

    //////////////////////////////////////////////////////////////////// STORE PARA CREAR O EDITAR
    // editar o crear item en la BD
    public function updateItem(){
        // normalizar
        $this->name = str_replace(' ', '', \Illuminate\Support\Str::title(trim($this->name)));
        $this->slug = \Illuminate\Support\Str::slug($this->name . '-' . \Illuminate\Support\Facades\Auth::id());

        if($this->tag){
            // validar
            $validatedData = $this->validate();

            // actualizar item en BD
            $this->tag->update($validatedData);

            // mensaje de success
            session()->flash('success', 'Editado correctamente');

        }else{
            // datos automaticos
            $this->user_id = \Illuminate\Support\Facades\Auth::id();
            $this->uuid = \Illuminate\Support\Str::random(24);

            // validar
            $validatedData = $this->validate();

            // crear en BD
            Tag::create($validatedData);
            
            // mensaje de success
            session()->flash('success', 'Creado correctamente');
        }

        // redireccionar
        $this->redirectRoute('tags.index', navigate:true);
    }
};
?>

<div>
    {{-- titulo, descripcion y breadcrumbs --}}
    <x-page.partials.title-page 
        :title="$this->titlePage"
        :create-route="'tags.index'"
        icon="arrow-uturn-left"
        :breadcrumbs="[
            ['label' => 'Dashboard', 'route' => 'dashboard'],
            ['label' => 'Asociaciones', 'route' => 'associations.index'],
            ['label' => 'Etiquetas', 'route' => 'tags.index'],
            ['label' => $this->titlePage]
        ]"
    />

    {{-- formulario completo --}}
    <div class="space-y-2">
        <flux:input type="text" label="Nombre" wire:model="name" placeholder="Nombre del sujeto" autofocus/>

        <x-libraries.utilities.errors />

        <flux:button :icon="$tag ? 'pencil-square' : 'plus'" wire:click="updateItem">{{ $this->buttonSubmit }}</flux:button>
    </div>
</div>