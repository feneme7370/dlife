<?php

use App\Models\Page\Collection;
use Livewire\Component;

new class extends Component
{
    //propiedades de titulos
    public string $title = 'Agregar saga';
    public string $subtitle = 'Agregue una saga a la lista';

    // propiedades del item
    public string $name = '';
    public string $slug = '';
    public int $books_amount = 0;
    public int $movies_amount = 0;
    public string $description = '';
    public string $cover_image_url = '';
    public string $uuid = '';
    public int $user_id = 0;

    // reglas de validacion
    protected function rules(){
        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', \Illuminate\Validation\Rule::unique('collections', 'slug')->ignore($this->subject?->id ?? 0)],
            'description' => ['nullable', 'string'],
            'books_amount' => ['nullable', 'numeric'],
            'movies_amount' => ['nullable', 'numeric'],
            'cover_image_url' => ['nullable', 'url', 'max:65535'],
            'uuid' => ['required', 'string', 'max:255', \Illuminate\Validation\Rule::unique('collections', 'uuid')->ignore($this->subject?->id ?? 0)],
            'user_id' => ['required', 'exists:users,id'],
        ];
    }

    // renombrar variables a castellano
    protected $validationAttributes = [
        'name' => 'nombre',
        'slug' => 'nombre url',
        'description' => 'descripcion',
        'books_amount' => 'total de libros',
        'movies_amount' => 'total de peliculas',
        'cover_image_url' => 'imagen web',
        'uuid' => 'uuid',
        'user_id' => 'usuario',
    ];

    // crear item en la BD
    public function storeItem(){
        // datos automaticos
        $this->user_id = \Illuminate\Support\Facades\Auth::id();
        $this->slug = \Illuminate\Support\Str::slug($this->name . '-' . \Illuminate\Support\Str::random(4));
        $this->uuid = \Illuminate\Support\Str::random(24);

        // validar
        $validatedData = $this->validate();

        // crear en BD
        Collection::create($validatedData);

        // mensaje de success
        session()->flash('success', 'Creado correctamente');

        // redireccionar
        $this->redirectRoute('collections.index', navigate:true);
    }
};
?>

<div>
    {{-- titulo, descripcion y breadcrumbs --}}
    <div>
        <flux:main class="mb-1 space-y-1">
            <flux:heading size="xl" level="1">
                <a href="{{ route('collections.index') }}"><flux:button size="xs" variant="ghost" icon="arrow-uturn-left"></flux:button></a>
                {{ $this->title }}
            </flux:heading>
            <flux:text class="text-base">{{ $this->subtitle }}</flux:text>
    
            <flux:breadcrumbs>
                <flux:breadcrumbs.item href="{{ route('dashboard') }}">Dashboard</flux:breadcrumbs.item>
                <flux:breadcrumbs.item href="{{ route('collections.index') }}">Colecciones</flux:breadcrumbs.item>
                <flux:breadcrumbs.item>{{ $this->title }}</flux:breadcrumbs.item>
            </flux:breadcrumbs>
    
            <flux:separator variant="subtle" />
        </flux:main>
    </div>

    <div class="space-y-2">
        <flux:input type="text" label="Nombre" wire:model="name" placeholder="Nombre del saga" autofocus/>
        <flux:input type="text" label="Link de imagen" wire:model="cover_image_url" placeholder="Pegue el link de una imagen"/>
        
        <flux:input type="number" label="Total de libros" wire:model="books_amount" placeholder="Total de libros"/>
        <flux:input type="number" label="Total de peliculas" wire:model="movies_amount" placeholder="Total de peliculas"/>
        
        <flux:textarea
            label="Descripcion"
            placeholder="Coloque una descripcion del saga"
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

        <flux:button icon="plus" wire:click="storeItem">Agregar</flux:button>
    </div>
</div>