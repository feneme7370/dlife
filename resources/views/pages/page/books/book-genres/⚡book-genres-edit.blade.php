<?php

use App\Models\Page\BookGenre;
use Livewire\Component;

new class extends Component
{
    //propiedades de titulos
    public string $titlePage = '';
    public string $subtitlePage = '';
    public string $buttonSubmit = '';
    
    // propiedades del item
    public $bookGenre;
    public ?string $name = null;
    public ?string $slug = null;
    public ?string $name_general = null;
    public ?string $slug_general = null;
    public ?string $description = null;
    public ?string $cover_image_url = null;
    public ?string $uuid = null;
    public ?int $user_id = null;

    // precargar datos al iniciar pagina
    public function mount($bookGenreUuid = null){
        $this->bookGenre = BookGenre::where('uuid', $bookGenreUuid)->first();

        $this->titlePage = $this->bookGenre ? 'Modificar genero' : 'Agregar genero';
        $this->subtitlePage = $this->bookGenre ? 'Modificar datos del genero' : 'Agregar datos del genero';
        $this->buttonSubmit = $this->bookGenre ? 'Modificar' : 'Agregar';

        $this->name = $this->bookGenre?->name ?? null;
        $this->slug = $this->bookGenre?->slug ?? null;
        $this->name_general = $this->bookGenre?->name_general ?? null;
        $this->slug_general = $this->bookGenre?->slug_general ?? null;
        $this->description = $this->bookGenre?->description ?? null;
        $this->cover_image_url = $this->bookGenre?->cover_image_url ?? null;
        $this->uuid = $this->bookGenre?->uuid ?? null;
        $this->user_id = $this->bookGenre?->user_id ?? \Illuminate\Support\Facades\Auth::id();
    }

    // reglas de validacion
    protected function rules(){
        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', \Illuminate\Validation\Rule::unique('book_genres', 'slug')->ignore($this->bookGenre?->id ?? 0)],
            'name_general' => ['required', 'string', 'max:255'],
            'slug_general' => ['required', 'string', 'max:255', \Illuminate\Validation\Rule::unique('book_genres', 'slug')->ignore($this->bookGenre?->id ?? 0)],
            'description' => ['nullable', 'string'],
            'cover_image_url' => ['nullable', 'url', 'max:65535'],
            'uuid' => ['required', 'string', 'max:255', \Illuminate\Validation\Rule::unique('book_genres', 'uuid')->ignore($this->bookGenre?->id ?? 0)],
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

    public function updateItem(){
        // normalizar
        $this->name = \Illuminate\Support\Str::title(trim($this->name));
        $this->slug = \Illuminate\Support\Str::slug($this->name . '-' . \Illuminate\Support\Str::random(4));
        $this->name_general = \Illuminate\Support\Str::title(trim($this->name_general));
        $this->slug_general = \Illuminate\Support\Str::slug($this->name_general . '-' . \Illuminate\Support\Str::random(4));

        if($this->bookGenre){
            // validar
            $validatedData = $this->validate();

            // actualizar item en BD
            $this->bookGenre->update($validatedData);

            // mensaje de success
            session()->flash('success', 'Editado correctamente');

        }else{
            // datos automaticos
            $this->user_id = \Illuminate\Support\Facades\Auth::id();
            $this->uuid = \Illuminate\Support\Str::random(24);

            // validar
            $validatedData = $this->validate();

            // crear en BD
            BookGenre::create($validatedData);
            
            // mensaje de success
            session()->flash('success', 'Creado correctamente');
        }

        // redireccionar
        $this->redirectRoute('book-genres.index', navigate:true);
    }
};
?>

<div>
    {{-- titulo, descripcion y breadcrumbs --}}
    <div>
        <div class="mb-1 space-y-1">
            <flux:heading size="xl" level="1">
                <a href="{{ route('book-genres.index') }}"><flux:button size="xs" variant="ghost" icon="arrow-uturn-left"></flux:button></a>
                {{ $this->titlePage }}
            </flux:heading>
            <flux:text class="text-base">{{ $this->subtitlePage }}</flux:text>
    
            <flux:breadcrumbs>
                <flux:breadcrumbs.item href="{{ route('dashboard') }}">Dashboard</flux:breadcrumbs.item>
                <flux:breadcrumbs.item href="{{ route('book-genres.index') }}">Generos</flux:breadcrumbs.item>
                <flux:breadcrumbs.item>{{ $this->titlePage }}</flux:breadcrumbs.item>
            </flux:breadcrumbs>
    
            <flux:separator variant="subtle" />
        </div>
    </div>

    <div class="space-y-2">
        <flux:input type="text" label="Nombre" wire:model="name" placeholder="Nombre del genero" autofocus/>
        <flux:input type="text" label="Nombre general" wire:model="name_general" placeholder="Nombre general"/>
        <flux:input type="text" label="Link de imagen" wire:model="cover_image_url" placeholder="Pegue el link de una imagen"/>
        <flux:textarea
            label="Descripcion"
            placeholder="Coloque una descripcion del genero"
            wire:model="description"
            rows="5"
        />

        <x-libraries.utilities.errors />

        <flux:button :icon="$bookGenre ? 'pencil-square' : 'plus'" wire:click="updateItem">{{ $this->buttonSubmit }}</flux:button>
    </div>
</div>