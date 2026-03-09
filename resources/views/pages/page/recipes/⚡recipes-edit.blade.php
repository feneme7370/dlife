<?php

use App\Models\Page\Rcategory;
use App\Models\Page\Recipe;
use Livewire\Component;

new class extends Component
{
    //propiedades de titulos
    public string $titlePage = '';
    public string $subtitlePage = '';
    public string $buttonSubmit = '';
    
    // propiedades del item
    public $recipe;
    public ?string $title = null;
    public ?string $slug = null;
    public ?string $description = null;
    public ?string $ingredients = null;
    public ?string $ingredients_clear = null;
    public ?string $instructions = null;
    public ?string $instructions_clear = null;
    public ?string $cover_image_url = null;
    public ?string $uuid = null;
    public ?int $user_id = null;

    // propiedades para relacion muchos a muchos
    public $selectedRecipeCategories = [];
    public $selectedRecipeTags = [];

    // precargar datos al iniciar pagina
    public function mount($recipeUuid = null){
        $this->recipe = Recipe::where('uuid', $recipeUuid)->first();
        
        $this->titlePage = $this->recipe ? 'Modificar receta' : 'Agregar receta';
        $this->subtitlePage = $this->recipe ? 'Modificar datos del receta' : 'Agregar datos del receta';
        $this->buttonSubmit = $this->recipe ? 'Modificar' : 'Agregar';

        $this->title = $this->recipe?->title ?? null;
        $this->slug = $this->recipe?->slug ?? null;
        $this->description = $this->recipe?->description ?? null;
        $this->ingredients = $this->recipe?->ingredients ?? null;
        $this->ingredients_clear = $this->recipe?->ingredients_clear ?? null;
        $this->instructions = $this->recipe?->instructions ?? null;
        $this->instructions_clear = $this->recipe?->instructions_clear ?? null;

        $this->cover_image_url = $this->recipe?->cover_image_url ?? null;
        $this->uuid = $this->recipe?->uuid ?? null;
        $this->user_id = $this->recipe?->user_id ?? \Illuminate\Support\Facades\Auth::id();

        // poner en arrays las asociaciones de m2m
        $this->selectedRecipeCategories = $this->recipe?->categories->pluck('id')->toArray() ?? [];
        $this->selectedRecipeTags = $this->recipe?->tags->pluck('name')->toArray() ?? [];
    }

    //////////////////////////////////////////////////////////////////// DATOS PARA ASOCIAR
    // traer datos de generos para asociar
    public function categories(){
        return Rcategory::where('user_id', \Illuminate\Support\Facades\Auth::id())
            ->orderBy('name', 'asc')
            ->get();
    }

    //////////////////////////////////////////////////////////////////// REGLAS DE VALIDACION
    // reglas de validacion
    protected function rules(){
        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', \Illuminate\Validation\Rule::unique('recipes', 'slug')->ignore($this->recipe?->id ?? 0)],
            'description' => ['nullable', 'string'],
            'ingredients' => ['nullable', 'string'],
            'ingredients_clear' => ['nullable', 'string'],
            'instructions' => ['nullable', 'string'],
            'instructions_clear' => ['nullable', 'string'],
            'cover_image_url' => ['nullable', 'url', 'max:65535'],
            'uuid' => ['required', 'string', 'max:255', \Illuminate\Validation\Rule::unique('recipes', 'uuid')->ignore($this->recipe?->id ?? 0)],
            'user_id' => ['required', 'exists:users,id'],
        ];
    }

    // renombrar variables a castellano
    protected $validationAttributes = [
        'title' => 'nombre',
        'slug' => 'nombre url',
        'description' => 'descripcion',
        'ingredients' => 'ingredientes',
        'ingredients_clear' => 'ingredientes limpios',
        'instructions' => 'instrucciones',
        'instructions_clear' => 'instrucciones limpias',
        'cover_image_url' => 'imagen web',
        'uuid' => 'uuid',
        'user_id' => 'usuario',
    ];

    // editar o crear item en la BD
    public function updateItem(){
        // normalizar
        $this->title = \Illuminate\Support\Str::title(trim($this->title));
        $this->slug = \Illuminate\Support\Str::slug($this->title . '-' . \Illuminate\Support\Str::random(4));
        $this->instructions_clear = $this->cleanNotes($this->instructions);
        $this->ingredients_clear = $this->cleanNotes($this->ingredients);

        if($this->recipe){
            // validar
            $validatedData = $this->validate();

            // actualizar item en BD
            $this->recipe->update($validatedData);
            $this->recipe->categories()->sync($this->selectedRecipeCategories);

            // agregar tags
            $tagIds = [];
            foreach ($this->selectedRecipeTags as $tagName) {
                $tag = \App\Models\Page\Rtag::firstOrCreate(
                    ['name' => $tagName],
                    [
                        'slug' => \Illuminate\Support\Str::slug($tagName),
                        'uuid' => \Illuminate\Support\Str::random(24),
                        'user_id' => \Illuminate\Support\Facades\Auth::id(),
                    ]
                );

                $tagIds[] = $tag->id;
            }
            $this->recipe->tags()->sync($tagIds);

            // mensaje de success
            session()->flash('success', 'Editado correctamente');

        }else{
            // datos automaticos
            $this->user_id = \Illuminate\Support\Facades\Auth::id();
            $this->uuid = \Illuminate\Support\Str::random(24);

            // validar
            $validatedData = $this->validate();

            // crear en BD
            $s = Recipe::create($validatedData);
            $s->categories()->sync($this->selectedRecipeCategories);

            // agregar tags
            $tagIds = [];
            foreach ($this->selectedRecipeTags as $tagName) {
                $tag = \App\Models\Page\Rtag::firstOrCreate(
                    ['name' => $tagName],
                    [
                        'slug' => \Illuminate\Support\Str::slug($tagName),
                        'uuid' => \Illuminate\Support\Str::random(24),
                        'user_id' => \Illuminate\Support\Facades\Auth::id(),
                    ]
                );

                $tagIds[] = $tag->id;
            }
            $s->tags()->sync($tagIds);
            
            // mensaje de success
            session()->flash('success', 'Creado correctamente');
        }

        // redireccionar
        $this->redirectRoute('recipes.index', navigate:true);
    }

    //////////////////////////////////////////////////////////////////// STORE PARA TAGS
    // store para crear tags
    public $name_tag;
    public $newTag = '';   // input actual
    // public $selectedBookTags = [];     // array de tags agregados

    public function addTag()
    {
        $formatted = str_replace(' ', '', \Illuminate\Support\Str::title(trim($this->newTag)));

        if ($formatted && !in_array($formatted, $this->selectedRecipeTags)) {
            $this->selectedRecipeTags[] = $formatted;
        }

        $this->newTag = '';
    }

    public function removeTag($index)
    {
        unset($this->selectedRecipeTags[$index]);
        $this->selectedRecipeTags = array_values($this->selectedRecipeTags); // reindexa
    }

   public function cleanNotes(?string $html): string
    {
        if (!$html) return '';

        $text = str_replace(
            ['</p>', '<br>', '<br/>', '<br />'],
            "\n",
            $html
        );

        return trim(
            html_entity_decode(
                strip_tags($text),
                ENT_QUOTES | ENT_HTML5,
                'UTF-8'
            )
        );
    }
};
?>

<div>
    {{-- titulo, descripcion y breadcrumbs --}}
    <div>
        <div class="mb-1 space-y-1">
            <flux:heading size="xl" level="1">
                <a href="{{ route('recipes.index') }}"><flux:button size="xs" variant="ghost" icon="arrow-uturn-left"></flux:button></a>
                {{ $this->titlePage }}
            </flux:heading>
            <flux:text class="text-base">{{ $this->subtitlePage }}</flux:text>
    
            <flux:breadcrumbs>
                <flux:breadcrumbs.item href="{{ route('dashboard') }}">Dashboard</flux:breadcrumbs.item>
                <flux:breadcrumbs.item href="{{ route('recipes.index') }}">Recetas</flux:breadcrumbs.item>
                <flux:breadcrumbs.item>{{ $this->titlePage }}</flux:breadcrumbs.item>
            </flux:breadcrumbs>
    
            <flux:separator variant="subtle" />
        </div>
    </div>

    <div class="space-y-2">
        <flux:input type="text" label="Nombre" wire:model="title" placeholder="Nombre del sujeto" autofocus/>
        <flux:input type="text" label="Link de imagen" wire:model="cover_image_url" placeholder="Pegue el link de una imagen"/>
        
        <flux:textarea
            label="Descripcion"
            placeholder="Coloque una descripcion del sujeto"
            wire:model="description"
            rows="5"
        />
        
        <flux:label>Ingredientes</flux:label>
        <x-libraries.quill-textarea-form 
            id_quill="editor_create_ingredients" 
            name="ingredients"
            rows="15" 
            placeholder="{{ __('Ingredientes') }}" model="ingredients"
            model_data="{{ $ingredients }}" 
        />

        <flux:label>Instrucciones</flux:label>
        <x-libraries.quill-textarea-form 
        id_quill="editor_create_instructions" 
        name="instructions"
        rows="15" 
        placeholder="{{ __('Instrucciones') }}" model="instructions"
        model_data="{{ $instructions }}" 
        />

        <flux:select wire:model="selectedRecipeCategories" label="Categoria">
            <option value="">Seleccionar categoria</option>
            @foreach ($this->categories() as $item)
                <option value="{{ $item->id }}">{{ $item->name }}</option>
            @endforeach
        </flux:select>

        <flux:input type="text" label="Etiquetas" wire:model="newTag" wire:keydown.period.prevent="addTag" placeholder="Agregue etiquetas" />
        <div class="flex gap-2 mt-2">
            @foreach($selectedRecipeTags as $index => $tag)
                <flux:badge size="sm" color="purple">
                    <button class="mr-2" wire:click="removeTag({{ $index }})">
                        x
                    </button>
                    #{{ $tag }}
                </flux:badge>
            @endforeach
        </div>

        <x-libraries.utilities.errors />

        <flux:button :icon="$recipe ? 'pencil-square' : 'plus'" wire:click="updateItem">{{ $this->buttonSubmit }}</flux:button>
    </div>
</div>