<?php

use App\Models\Page\Dcategory;
use Livewire\Component;

new class extends Component
{
    //propiedades de titulos
    public string $title = 'Editar categoria';
    public string $subtitle = 'Edite una categoria de la lista';
    
    // propiedades del item
    public $dcategory;
    public string $name = '';
    public string $slug = '';
    public string $uuid = '';
    public int $user_id = 0;

    // precargar datos al iniciar pagina
    public function mount($dcategoryUuid){
        $this->dcategory = Dcategory::where('uuid', $dcategoryUuid)->first();

        $this->name = $this->dcategory->name ?? '';
        $this->slug = $this->dcategory->slug ?? '';
        $this->uuid = $this->dcategory->uuid ?? '';
        $this->user_id = $this->dcategory->user_id ?? 0;
    }

    // reglas de validacion
    protected function rules(){
        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', \Illuminate\Validation\Rule::unique('dcategories', 'slug')->ignore($this->dcategory?->id ?? 0)],
            'uuid' => ['required', 'string', 'max:255', \Illuminate\Validation\Rule::unique('dcategories', 'uuid')->ignore($this->dcategory?->id ?? 0)],
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

    // editar item en la BD
    public function updateItem(){
        // datos automaticos
        $this->slug = \Illuminate\Support\Str::slug($this->name . '-' . \Illuminate\Support\Str::random(4));

        // validar
        $validatedData = $this->validate();

        // actualizar item en BD
        $this->dcategory->update($validatedData);

        // mensaje de success
        session()->flash('success', 'Editado correctamente');

        // redireccionar
        $this->redirectRoute('dcategories.index', navigate:true);
    }
};
?>

<div>
    {{-- titulo, descripcion y breadcrumbs --}}
    <div>
        <div class="mb-1 space-y-1">
            <flux:heading size="xl" level="1">
                <a href="{{ route('dcategories.index') }}"><flux:button size="xs" variant="ghost" icon="arrow-uturn-left"></flux:button></a>
                {{ $this->title }}
            </flux:heading>
            <flux:text class="text-base">{{ $this->subtitle }}</flux:text>
    
            <flux:breadcrumbs>
                <flux:breadcrumbs.item href="{{ route('dashboard') }}">Dashboard</flux:breadcrumbs.item>
                <flux:breadcrumbs.item href="{{ route('diaries.index') }}">Libros</flux:breadcrumbs.item>
                <flux:breadcrumbs.item href="{{ route('dcategories.index') }}">Categorias</flux:breadcrumbs.item>
                <flux:breadcrumbs.item>{{ $this->title }}</flux:breadcrumbs.item>
            </flux:breadcrumbs>
    
            <flux:separator variant="subtle" />
        </div>
    </div>

    <div class="space-y-2">
        <flux:input type="text" label="Nombre" wire:model="name" placeholder="Nombre de la categoria" autofocus/>

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