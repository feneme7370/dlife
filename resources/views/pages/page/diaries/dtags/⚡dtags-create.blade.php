<?php

use App\Models\Page\Dtag;
use Livewire\Component;

new class extends Component
{
    //propiedades de titulos
    public string $title = 'Agregar etiqueta';
    public string $subtitle = 'Agregue una etiqueta a la lista';

    // propiedades del item
    public $dtag;
    public string $name = '';
    public string $slug = '';
    public string $uuid = '';
    public int $user_id = 0;

    // reglas de validacion
    protected function rules(){
        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', \Illuminate\Validation\Rule::unique('dtags', 'slug')->ignore($this->dtag?->id ?? 0)],
            'uuid' => ['required', 'string', 'max:255', \Illuminate\Validation\Rule::unique('dtags', 'uuid')->ignore($this->dtag?->id ?? 0)],
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

    // crear item en la BD
    public function storeItem(){
        $this->name = \Illuminate\Support\Str::of($this->name)
                ->lower()
                ->title()
                ->replace(' ', '');

        // datos automaticos
        $this->user_id = \Illuminate\Support\Facades\Auth::id();
        $this->slug = \Illuminate\Support\Str::slug($this->name . '-' . \Illuminate\Support\Str::random(4));
        $this->uuid = \Illuminate\Support\Str::random(24);

        // validar
        $validatedData = $this->validate();

        // crear en BD
        Dtag::create($validatedData);

        // mensaje de success
        session()->flash('success', 'Creado correctamente');

        // redireccionar
        $this->redirectRoute('dtags.index', navigate:true);
    }
};
?>

<div>
    {{-- titulo, descripcion y breadcrumbs --}}
    <div>
        <div class="mb-1 space-y-1">
            <flux:heading size="xl" level="1">
                <a href="{{ route('dtags.index') }}"><flux:button size="xs" variant="ghost" icon="arrow-uturn-left"></flux:button></a>
                {{ $this->title }}
            </flux:heading>
            <flux:text class="text-base">{{ $this->subtitle }}</flux:text>
    
            <flux:breadcrumbs>
                <flux:breadcrumbs.item href="{{ route('dashboard') }}">Dashboard</flux:breadcrumbs.item>
                <flux:breadcrumbs.item href="{{ route('diaries.index') }}">Libros</flux:breadcrumbs.item>
                <flux:breadcrumbs.item href="{{ route('dtags.index') }}">Etiquetas</flux:breadcrumbs.item>
                <flux:breadcrumbs.item>{{ $this->title }}</flux:breadcrumbs.item>
            </flux:breadcrumbs>
    
            <flux:separator variant="subtle" />
        </div>
    </div>

    <div class="space-y-2">
        <flux:input type="text" label="Nombre" wire:model="name" placeholder="Nombre del etiqueta" autofocus/>

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
</div>