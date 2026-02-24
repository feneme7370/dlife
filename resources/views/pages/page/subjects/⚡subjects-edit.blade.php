<?php

use App\Models\Page\Subject;
use Livewire\Component;

new class extends Component
{
    //propiedades de titulos
    public string $title = 'Editar sujetos';
    public string $subtitle = 'Edite un sujetos a la lista';
    
    // propiedades del item
    public $subject;
    public string $name = '';
    public string $slug = '';
    public string $country = '';
    public string $birthdate = '';
    public string $description = '';
    public string $cover_image_url = '';
    public string $uuid = '';
    public int $user_id = 0;

    // precargar datos al iniciar pagina
    public function mount($subjectUuid){
        $this->subject = Subject::where('uuid', $subjectUuid)->first();

        $this->name = $this->subject->name ?? '';
        $this->slug = $this->subject->slug ?? '';
        $this->country = $this->subject->country ?? '';
        $this->birthdate = $this->subject->birthdate ?? '';
        $this->description = $this->subject->description ?? '';
        $this->cover_image_url = $this->subject->cover_image_url ?? '';
        $this->uuid = $this->subject->uuid ?? '';
        $this->user_id = $this->subject->user_id ?? 0;
    }

    // reglas de validacion
    protected function rules(){
        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', \Illuminate\Validation\Rule::unique('subjects', 'slug')->ignore($this->subject?->id ?? 0)],
            'birthdate' => ['nullable', 'date'],
            'country' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'cover_image_url' => ['nullable', 'url', 'max:65535'],
            'uuid' => ['required', 'string', 'max:255', \Illuminate\Validation\Rule::unique('subjects', 'uuid')->ignore($this->subject?->id ?? 0)],
            'user_id' => ['required', 'exists:users,id'],
        ];
    }

    // renombrar variables a castellano
    protected $validationAttributes = [
        'name' => 'nombre',
        'slug' => 'nombre url',
        'birthdate' => 'fecha de nacimiento',
        'country' => 'pais',
        'description' => 'descripcion',
        'cover_image_url' => 'imagen web',
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
        $this->subject->update($validatedData);

        // mensaje de success
        session()->flash('success', 'Editado correctamente');

        // redireccionar
        $this->redirectRoute('subjects.index', navigate:true);
    }
};
?>

<div>
    {{-- titulo, descripcion y breadcrumbs --}}
    <div>
        <flux:main class="mb-1 space-y-1">
            <flux:heading size="xl" level="1">
                <a href="{{ route('subjects.index') }}"><flux:button size="xs" variant="ghost" icon="arrow-uturn-left"></flux:button></a>
                {{ $this->title }}
            </flux:heading>
            <flux:text class="text-base">{{ $this->subtitle }}</flux:text>
    
            <flux:breadcrumbs>
                <flux:breadcrumbs.item href="{{ route('dashboard') }}">Dashboard</flux:breadcrumbs.item>
                <flux:breadcrumbs.item href="{{ route('subjects.index') }}">Sujetos</flux:breadcrumbs.item>
                <flux:breadcrumbs.item>{{ $this->title }}</flux:breadcrumbs.item>
            </flux:breadcrumbs>
    
            <flux:separator variant="subtle" />
        </flux:main>
    </div>

    <div class="space-y-2">
        <flux:input type="text" label="Nombre" wire:model="name" placeholder="Nombre del sujeto" autofocus/>
        <flux:input type="date" max="2999-12-31" label="Fecha de nacimiento" wire:model="birthdate"/>
        <flux:input type="text" label="Pais de nacimiento" wire:model="country" placeholder="Pais de nacimiento"/>
        <flux:input type="text" label="Link de imagen" wire:model="cover_image_url" placeholder="Pegue el link de una imagen"/>
        <flux:textarea
            label="Descripcion"
            placeholder="Coloque una descripcion del sujeto"
            wire:model="description"
            rows="10"
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