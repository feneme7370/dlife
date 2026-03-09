<?php

use App\Models\Page\Rcategory;
use Livewire\Component;

new class extends Component
{
    //propiedades de titulos
    public string $titlePage = '';
    public string $subtitlePage = '';
    public string $buttonSubmit = '';
    
    // propiedades del item
    public $rcategory;
    public ?string $name = null;
    public ?string $slug = null;
    public ?string $uuid = null;
    public ?int $user_id = null;

    // precargar datos al iniciar pagina
    public function mount($rcategoryUuid = null){
        $this->rcategory = Rcategory::where('uuid', $rcategoryUuid)->first();

        $this->titlePage = $this->rcategory ? 'Modificar categoria' : 'Agregar categoria';
        $this->subtitlePage = $this->rcategory ? 'Modificar datos del categoria' : 'Agregar datos del categoria';
        $this->buttonSubmit = $this->rcategory ? 'Modificar' : 'Agregar';

        $this->name = $this->rcategory?->name ?? null;
        $this->slug = $this->rcategory?->slug ?? null;
        $this->uuid = $this->rcategory?->uuid ?? null;
        $this->user_id = $this->rcategory?->user_id ?? \Illuminate\Support\Facades\Auth::id();
    }

    // reglas de validacion
    protected function rules(){
        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', \Illuminate\Validation\Rule::unique('rcategories', 'slug')->ignore($this->rcategory?->id ?? 0)],
            'uuid' => ['required', 'string', 'max:255', \Illuminate\Validation\Rule::unique('rcategories', 'uuid')->ignore($this->rcategory?->id ?? 0)],
            'user_id' => ['required', 'exists:users,id'],
        ];
    }

    // renombrar variables a castellano
    protected $validationAttributes = [
        'name' => 'nombre',
        'slug' => 'nombre url',
        'uuid' => 'uuid',
        'user_id' => 'usuario',
    ];

    // editar o crear item en la BD
    public function updateItem(){
        // normalizar
        $this->name = \Illuminate\Support\Str::title(trim($this->name));
        $this->slug = \Illuminate\Support\Str::slug($this->name . '-' . \Illuminate\Support\Str::random(4));

        if($this->rcategory){
            // validar
            $validatedData = $this->validate();

            // actualizar item en BD
            $this->rcategory->update($validatedData);

            // mensaje de success
            session()->flash('success', 'Editado correctamente');

        }else{
            // datos automaticos
            $this->user_id = \Illuminate\Support\Facades\Auth::id();
            $this->uuid = \Illuminate\Support\Str::random(24);

            // validar
            $validatedData = $this->validate();

            // crear en BD
            Rcategory::create($validatedData);
            
            // mensaje de success
            session()->flash('success', 'Creado correctamente');
        }

        // redireccionar
        $this->redirectRoute('rcategories.index', navigate:true);
    }
};
?>

<div>
    {{-- titulo, descripcion y breadcrumbs --}}
    <div>
        <div class="mb-1 space-y-1">
            <flux:heading size="xl" level="1">
                <a href="{{ route('rcategories.index') }}"><flux:button size="xs" variant="ghost" icon="arrow-uturn-left"></flux:button></a>
                {{ $this->titlePage }}
            </flux:heading>
            <flux:text class="text-base">{{ $this->subtitlePage }}</flux:text>
    
            <flux:breadcrumbs>
                <flux:breadcrumbs.item href="{{ route('dashboard') }}">Dashboard</flux:breadcrumbs.item>
                <flux:breadcrumbs.item href="{{ route('diaries.index') }}">Libros</flux:breadcrumbs.item>
                <flux:breadcrumbs.item href="{{ route('rcategories.index') }}">Categorias</flux:breadcrumbs.item>
                <flux:breadcrumbs.item>{{ $this->titlePage }}</flux:breadcrumbs.item>
            </flux:breadcrumbs>
    
            <flux:separator variant="subtle" />
        </div>
    </div>

    <div class="space-y-2">
        <flux:input type="text" label="Nombre" wire:model="name" placeholder="Nombre de la categoria" autofocus/>

        <x-libraries.utilities.errors />

        <flux:button :icon="$rcategory ? 'pencil-square' : 'plus'" wire:click="updateItem">{{ $this->buttonSubmit }}</flux:button>
    </div>
</div>