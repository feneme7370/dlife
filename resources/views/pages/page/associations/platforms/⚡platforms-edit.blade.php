<?php

use App\Models\Page\Platform;
use Livewire\Component;

new class extends Component
{
    //////////////////////////////////////////////////////////////////// PROPIEDADES
    //propiedades de titulos
    public string $titlePage = '';
    public string $subtitlePage = '';
    public string $buttonSubmit = '';
    
    // propiedades del item
    public $platform;

    // propiedades del formulario
    public ?string $name = null;
    public ?string $brand = null;
    public ?string $release_year = null;

    public ?string $uuid = null;
    public ?int $user_id = null;

    //////////////////////////////////////////////////////////////////// PRE CARGAR DATOS
    // precargar datos al iniciar pagina
    public function mount($platformUuid = null){
        $this->platform = Platform::where('uuid', $platformUuid)->first();

        $this->titlePage = $this->platform ? 'Modificar plataforma' : 'Agregar plataforma';
        $this->subtitlePage = $this->platform ? 'Modificar datos de la plataforma' : 'Agregar datos de la plataforma';
        $this->buttonSubmit = $this->platform ? 'Modificar' : 'Agregar';

        $this->name = $this->platform?->name ?? null;
        $this->brand = $this->platform?->brand ?? null;
        $this->release_year = $this->platform?->release_year ?? null;

        $this->uuid = $this->platform?->uuid ?? null;
        $this->user_id = $this->platform?->user_id ?? \Illuminate\Support\Facades\Auth::id();

    }

    //////////////////////////////////////////////////////////////////// VALIDACIONES
    // reglas de validacion
    protected function rules(){
        return [
            'name' => ['required', 'string', 'max:255'],
            'brand' => ['nullable', 'string', 'max:255'],
            'release_year' => ['nullable', 'numeric'],
            'uuid' => ['required', 'string', 'max:255', \Illuminate\Validation\Rule::unique('platforms', 'uuid')->ignore($this->platform?->id ?? 0)],
            'user_id' => ['required', 'exists:users,id'],
        ];
    }

    // renombrar variables a castellano
    protected $validationAttributes = [
        'name' => 'nombre',
        'brand' => 'marca',
        'release_year' => 'año de lanzamiento',
        'uuid' => 'uuid',
        'user_id' => 'usuario',
    ];

    //////////////////////////////////////////////////////////////////// STORE PARA CREAR O EDITAR
    // editar o crear item en la BD
    public function updateItem(){
        // normalizar
        $this->name = \Illuminate\Support\Str::title(trim($this->name));

        if($this->platform){
            // validar
            $validatedData = $this->validate();

            // actualizar item en BD
            $this->platform->update($validatedData);

            // mensaje de success
            session()->flash('success', 'Editado correctamente');

        }else{
            // datos automaticos
            $this->user_id = \Illuminate\Support\Facades\Auth::id();
            $this->uuid = \Illuminate\Support\Str::random(24);

            // validar
            $validatedData = $this->validate();

            // crear en BD
            Platform::create($validatedData);
            
            // mensaje de success
            session()->flash('success', 'Creado correctamente');
        }

        // redireccionar
        $this->redirectRoute('platforms.index', navigate:true);
    }
};
?>

<div>
    {{-- titulo, descripcion y breadcrumbs --}}
    <x-page.partials.title-page 
        :title="$this->titlePage"
        :create-route="'platforms.index'"
        icon="arrow-uturn-left"
        :breadcrumbs="[
            ['label' => 'Dashboard', 'route' => 'dashboard'],
            ['label' => 'Asociaciones', 'route' => 'associations.index'],
            ['label' => 'Plataformas', 'route' => 'platforms.index'],
            ['label' => $this->titlePage]
        ]"
    />

    {{-- formulario completo --}}
    <div class="space-y-2">
        <flux:input type="text" label="Nombre" wire:model="name" placeholder="Nombre del genero" autofocus/>
        <flux:input type="text" label="Marca" wire:model="brand" placeholder="Marca" autofocus/>
        <flux:input type="number" label="año de lanzamiento" wire:model="release_year" placeholder="Año de lanzamiento" autofocus/>


        <x-libraries.utilities.errors />

        <flux:button :icon="$platform ? 'pencil-square' : 'plus'" wire:click="updateItem">{{ $this->buttonSubmit }}</flux:button>
    </div>
</div>