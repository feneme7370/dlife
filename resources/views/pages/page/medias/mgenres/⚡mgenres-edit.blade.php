<?php

use App\Models\Page\Mgenre;
use Livewire\Component;

new class extends Component
{
    //propiedades de titulos
    public string $title = 'Editar genero';
    public string $subtitle = 'Edite un genero a la lista';
    
    // propiedades del item
    public $mgenre;
    public string $name = '';
    public string $slug = '';
    public string $name_general = '';
    public string $slug_general = '';
    public string $description = '';
    public string $cover_image_url = '';
    public string $uuid = '';
    public int $user_id = 0;

    // precargar datos al iniciar pagina
    public function mount($mgenreUuid){
        $this->mgenre = Mgenre::where('uuid', $mgenreUuid)->first();

        $this->name = $this->mgenre->name ?? '';
        $this->slug = $this->mgenre->slug ?? '';
        $this->name_general = $this->mgenre->name_general ?? '';
        $this->slug_general = $this->mgenre->slug_general ?? '';
        $this->description = $this->mgenre->description ?? '';
        $this->cover_image_url = $this->mgenre->cover_image_url ?? '';
        $this->uuid = $this->mgenre->uuid ?? '';
        $this->user_id = $this->mgenre->user_id ?? 0;
    }

    // reglas de validacion
    protected function rules(){
        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', \Illuminate\Validation\Rule::unique('mgenres', 'slug')->ignore($this->mgenre?->id ?? 0)],
            'name_general' => ['required', 'string', 'max:255'],
            'slug_general' => ['required', 'string', 'max:255', \Illuminate\Validation\Rule::unique('mgenres', 'slug')->ignore($this->mgenre?->id ?? 0)],
            'description' => ['nullable', 'string'],
            'cover_image_url' => ['nullable', 'url', 'max:65535'],
            'uuid' => ['required', 'string', 'max:255', \Illuminate\Validation\Rule::unique('mgenres', 'uuid')->ignore($this->mgenre?->id ?? 0)],
            'user_id' => ['required', 'exists:users,id'],
        ];
    }

    // renombrar variables a castellano
    protected $validationAttributes = [
        'name' => 'nombre',
        'slug' => 'nombre url',
        'name_general' => 'nombre general',
        'slug_general' => 'nombre general url',
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
        $this->mgenre->update($validatedData);

        // mensaje de success
        session()->flash('success', 'Editado correctamente');

        // redireccionar
        $this->redirectRoute('mgenres.index', navigate:true);
    }
};
?>

<div>
    {{-- titulo, descripcion y breadcrumbs --}}
    <div>
        <flux:main class="mb-1 space-y-1">
            <flux:heading size="xl" level="1">
                <a href="{{ route('mgenres.index') }}"><flux:button size="xs" variant="ghost" icon="arrow-uturn-left"></flux:button></a>
                {{ $this->title }}
            </flux:heading>
            <flux:text class="text-base">{{ $this->subtitle }}</flux:text>
    
            <flux:breadcrumbs>
                <flux:breadcrumbs.item href="{{ route('dashboard') }}">Dashboard</flux:breadcrumbs.item>
                <flux:breadcrumbs.item href="{{ route('mgenres.index') }}">Generos</flux:breadcrumbs.item>
                <flux:breadcrumbs.item>{{ $this->title }}</flux:breadcrumbs.item>
            </flux:breadcrumbs>
    
            <flux:separator variant="subtle" />
        </flux:main>
    </div>

    <div class="space-y-2">
        <flux:input type="text" label="Nombre" wire:model="name" placeholder="Nombre del genero" autofocus/>
        <flux:input type="text" label="Nombre general" wire:model="name_general" placeholder="Nombre general"/>
        <flux:input type="text" label="Link de imagen" wire:model="cover_image_url" placeholder="Pegue el link de una imagen"/>
        <flux:textarea
            label="Descripcion"
            placeholder="Coloque una descripcion del genero"
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