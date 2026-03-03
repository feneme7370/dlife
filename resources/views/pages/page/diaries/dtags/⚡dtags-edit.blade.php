<?php

use App\Models\Page\Dtag;
use Livewire\Component;

new class extends Component
{
    //propiedades de titulos
    public string $title = 'Editar etiqueta';
    public string $subtitle = 'Edite una etiqueta de la lista';
    
    // propiedades del item
    public $dtag;
    public string $name = '';
    public string $slug = '';
    public string $uuid = '';
    public int $user_id = 0;

    // precargar datos al iniciar pagina
    public function mount($dtagUuid){
        $this->dtag = Dtag::where('uuid', $dtagUuid)->first();

        $this->name = $this->dtag->name ?? '';
        $this->slug = $this->dtag->slug ?? '';
        $this->uuid = $this->dtag->uuid ?? '';
        $this->user_id = $this->dtag->user_id ?? 0;
    }

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

    // editar item en la BD
    public function updateItem(){
        $this->name = \Illuminate\Support\Str::of($this->name)
                ->lower()
                ->title()
                ->replace(' ', '');
        // datos automaticos
        $this->slug = \Illuminate\Support\Str::slug($this->name . '-' . \Illuminate\Support\Str::random(4));

        // validar
        $validatedData = $this->validate();

        // actualizar item en BD
        $this->dtag->update($validatedData);

        // mensaje de success
        session()->flash('success', 'Editado correctamente');

        // redireccionar
        $this->redirectRoute('dtags.index', navigate:true);
    }
};
?>

<div>
    {{-- titulo, descripcion y breadcrumbs --}}
    <div>
        <flux:main class="mb-1 space-y-1">
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
        </flux:main>
    </div>

    <div class="space-y-2">
        <flux:input type="text" label="Nombre" wire:model="name" placeholder="Nombre de la etiqueta" autofocus/>

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