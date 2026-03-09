<?php

use App\Models\Page\Subject;
use Livewire\Component;

new class extends Component
{
    //propiedades de titulos
    public string $titlePage = '';
    public string $subtitlePage = '';
    public string $buttonSubmit = '';
    
    // propiedades del item
    public $subject;
    public ?string $name = null;
    public ?string $slug = null;
    public ?string $country = null;
    public ?string $birthdate = null;
    public ?string $description = null;
    public ?string $cover_image_url = null;
    public ?string $uuid = null;
    public ?int $user_id = null;

    // precargar datos al iniciar pagina
    public function mount($subjectUuid = null){
        $this->subject = Subject::where('uuid', $subjectUuid)->first();

        $this->titlePage = $this->subject ? 'Modificar sujeto' : 'Agregar sujeto';
        $this->subtitlePage = $this->subject ? 'Modificar datos del sujeto' : 'Agregar datos del sujeto';
        $this->buttonSubmit = $this->subject ? 'Modificar' : 'Agregar';

        $this->name = $this->subject?->name ?? null;
        $this->slug = $this->subject?->slug ?? null;
        $this->country = $this->subject?->country ?? null;
        $this->birthdate = $this->subject?->birthdate ? \Carbon\Carbon::parse($this->subject->birthdate)->format('Y-m-d') : null;
        $this->description = $this->subject?->description ?? null;
        $this->cover_image_url = $this->subject?->cover_image_url ?? null;
        $this->uuid = $this->subject?->uuid ?? null;
        $this->user_id = $this->subject?->user_id ?? \Illuminate\Support\Facades\Auth::id();
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

    // editar o crear item en la BD
    public function updateItem(){
        // normalizar
        $this->name = \Illuminate\Support\Str::title(trim($this->name));
        $this->slug = \Illuminate\Support\Str::slug($this->name . '-' . \Illuminate\Support\Str::random(4));

        if($this->subject){
            // validar
            $validatedData = $this->validate();

            // actualizar item en BD
            $this->subject->update($validatedData);

            // mensaje de success
            session()->flash('success', 'Editado correctamente');

        }else{
            // datos automaticos
            $this->user_id = \Illuminate\Support\Facades\Auth::id();
            $this->uuid = \Illuminate\Support\Str::random(24);

            // validar
            $validatedData = $this->validate();

            // crear en BD
            Subject::create($validatedData);
            
            // mensaje de success
            session()->flash('success', 'Creado correctamente');
        }

        // redireccionar
        $this->redirectRoute('subjects.index', navigate:true);
    }
};
?>

<div>
    {{-- titulo, descripcion y breadcrumbs --}}
    <div>
        <div class="mb-1 space-y-1">
            <flux:heading size="xl" level="1">
                <a href="{{ route('subjects.index') }}"><flux:button size="xs" variant="ghost" icon="arrow-uturn-left"></flux:button></a>
                {{ $this->titlePage }}
            </flux:heading>
            <flux:text class="text-base">{{ $this->subtitlePage }}</flux:text>
    
            <flux:breadcrumbs>
                <flux:breadcrumbs.item href="{{ route('dashboard') }}">Dashboard</flux:breadcrumbs.item>
                <flux:breadcrumbs.item href="{{ route('subjects.index') }}">Sujetos</flux:breadcrumbs.item>
                <flux:breadcrumbs.item>{{ $this->titlePage }}</flux:breadcrumbs.item>
            </flux:breadcrumbs>
    
            <flux:separator variant="subtle" />
        </div>
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
            rows="5"
        />

        <x-libraries.utilities.errors />

        <flux:button :icon="$subject ? 'pencil-square' : 'plus'" wire:click="updateItem">{{ $this->buttonSubmit }}</flux:button>
    </div>
</div>