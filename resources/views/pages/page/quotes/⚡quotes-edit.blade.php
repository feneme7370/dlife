<?php

use App\Models\Page\Quote;
use Livewire\Component;

new class extends Component
{
    //////////////////////////////////////////////////////////////////// PROPIEDADES
    //propiedades de titulos
    public string $titlePage = '';
    public string $subtitlePage = '';
    public string $buttonSubmit = '';
    
    // propiedades del item
    public $quote;

    // propiedades del formulario
    public ?string $content = null;
    public ?string $author = null;
    public ?string $source = null;
    public ?string $uuid = null;
    public ?int $user_id = null;

    //////////////////////////////////////////////////////////////////// PRE CARGAR DATOS
    // precargar datos al iniciar pagina
    public function mount($quoteUuid = null){
        $this->quote = Quote::where('uuid', $quoteUuid)->first();

        $this->titlePage = $this->quote ? 'Modificar frase' : 'Agregar frase';
        $this->subtitlePage = $this->quote ? 'Modificar datos del frase' : 'Agregar datos del frase';
        $this->buttonSubmit = $this->quote ? 'Modificar' : 'Agregar';

        $this->content = $this->quote?->content ?? null;
        $this->author = $this->quote?->author ?? null;
        $this->source = $this->quote?->source ?? null;
        $this->uuid = $this->quote?->uuid ?? null;
        $this->user_id = $this->quote?->user_id ?? \Illuminate\Support\Facades\Auth::id();
    }

    //////////////////////////////////////////////////////////////////// VALIDACIONES
    // reglas de validacion
    protected function rules(){
        return [
            'content' => ['nullable', 'string'],
            'author' => ['required', 'string', 'max:255'],
            'source' => ['nullable', 'string', 'max:255'],
            'uuid' => ['required', 'string', 'max:255', \Illuminate\Validation\Rule::unique('quotes', 'uuid')->ignore($this->quote?->id ?? 0)],
            'user_id' => ['required', 'exists:users,id'],
        ];
    }

    // renombrar variables a castellano
    protected $validationAttributes = [
        'content' => 'frase',
        'author' => 'autor',
        'source' => 'fuente',
        'uuid' => 'uuid',
        'user_id' => 'usuario',
    ];

    //////////////////////////////////////////////////////////////////// STORE PARA CREAR O EDITAR
    // editar o crear item en la BD
    public function updateItem(){
        // normalizar
        $this->content = trim($this->content);
        $this->author = \Illuminate\Support\Str::title(trim($this->author));
        $this->source = \Illuminate\Support\Str::title(trim($this->source));

        if($this->quote){
            // validar
            $validatedData = $this->validate();

            // actualizar item en BD
            $this->quote->update($validatedData);

        }else{
            // datos automaticos
            $this->user_id = \Illuminate\Support\Facades\Auth::id();
            $this->uuid = \Illuminate\Support\Str::random(24);

            // validar
            $validatedData = $this->validate();

            // crear en BD
            Quote::create($validatedData);
        }

        // mensaje de success
        session()->flash('success', $this->quote ? 'Editado correctamente' : 'Creado correctamente');
        
        // redireccionar
        $this->redirectRoute('quotes.index', navigate:true);
    }
};
?>

<div>
     {{-- titulo, descripcion y breadcrumbs --}}
    <x-page.partials.title-page 
        :title="$this->titlePage"
        :create-route="'quotes.index'"
        icon="arrow-uturn-left"
        :breadcrumbs="[
            ['label' => 'Dashboard', 'route' => 'dashboard'],
            ['label' => 'Frases', 'route' => 'quotes.index'],
            ['label' => $this->titlePage]
        ]"
    />

     {{-- toast de mensaje --}}
     <x-libraries.flux.toast-success />

    {{-- formulario completo --}}
    <div class="space-y-2">
        
        <flux:textarea
            label="Frase"
            placeholder="Coloque frase"
            wire:model="content"
            rows="10"
            autofocus
        />

        <flux:input type="text" label="Autor" wire:model="author" placeholder="Nombre del autor"/>
        <flux:input type="text" label="Fuente" wire:model="source" placeholder="Coloque la fuente"/>

        <x-libraries.utilities.errors />

        <flux:button :icon="$quote ? 'pencil-square' : 'plus'" wire:click="updateItem">{{ $this->buttonSubmit }}</flux:button>
    </div>
</div>