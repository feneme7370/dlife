<?php

use App\Models\Page\Blog;
use Livewire\Component;

new class extends Component
{
    use \App\Traits\HandlesTags;
    use \App\Traits\CleansHtml;

    //////////////////////////////////////////////////////////////////// PROPIEDADES
    //propiedades de titulos
    public string $titlePage = '';
    public string $subtitlePage = '';
    public string $buttonSubmit = '';
    
    // propiedades del item
    public $blog;
    public ?string $title = null;
    public ?string $slug = null;
    public ?string $excerpt = null;
    public ?string $type = null;
    public ?string $content = null;
    public ?string $content_clear = null;
    public ?string $cover_image_url = null;
    public ?string $uuid = null;
    public ?int $user_id = null;

    // propiedades para relacion muchos a muchos
    public $selectedBlogTags = [];

    //////////////////////////////////////////////////////////////////// PRE CARGAR DATOS
    // precargar datos al iniciar pagina
    public function mount($blogUuid = null){
        $this->blog = Blog::where('uuid', $blogUuid)->first();
        
        $this->titlePage = $this->blog ? 'Modificar blog' : 'Agregar blog';
        $this->subtitlePage = $this->blog ? 'Modificar datos del blog' : 'Agregar datos del blog';
        $this->buttonSubmit = $this->blog ? 'Modificar' : 'Agregar';

        $this->title = $this->blog?->title ?? null;
        $this->slug = $this->blog?->slug ?? null;
        $this->excerpt = $this->blog?->excerpt ?? null;
        $this->type = $this->blog?->type ?? null;
        $this->content = $this->blog?->content ?? null;
        $this->content_clear = $this->blog?->content_clear ?? null;

        $this->cover_image_url = $this->blog?->cover_image_url ?? null;
        $this->uuid = $this->blog?->uuid ?? null;
        $this->user_id = $this->blog?->user_id ?? \Illuminate\Support\Facades\Auth::id();

        // poner en arrays las asociaciones de m2m
        $this->selectedBlogTags = $this->blog?->tags->pluck('name')->toArray() ?? [];
    }

    //////////////////////////////////////////////////////////////////// VALIDACIONES
    // reglas de validacion
    protected function rules(){
        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', \Illuminate\Validation\Rule::unique('blogs', 'slug')->ignore($this->blog?->id ?? 0)],
            'excerpt' => ['required', 'string'],
            'type' => ['required', 'string'],
            'content' => ['nullable', 'string'],
            'content_clear' => ['nullable', 'string'],
            'cover_image_url' => ['nullable', 'url', 'max:65535'],
            'uuid' => ['required', 'string', 'max:255', \Illuminate\Validation\Rule::unique('blogs', 'uuid')->ignore($this->blog?->id ?? 0)],
            'user_id' => ['required', 'exists:users,id'],
        ];
    }

    // renombrar variables a castellano
    protected $validationAttributes = [
        'title' => 'nombre',
        'slug' => 'nombre url',
        'excerpt' => 'descripcion',
        'type' => 'tipo',
        'content' => 'contenido',
        'content_clear' => 'contenido limpio',
        'cover_image_url' => 'imagen web',
        'uuid' => 'uuid',
        'user_id' => 'usuario',
    ];

    //////////////////////////////////////////////////////////////////// STORE PARA CREAR O EDITAR
    // cosultas
    public function types(){
        return \App\Models\Page\Blog::types();
    }

    //////////////////////////////////////////////////////////////////// STORE PARA CREAR O EDITAR
    // editar o crear item en la BD
    public function updateItem(){
        // normalizar
        $this->title = \Illuminate\Support\Str::title(trim($this->title));
        $this->slug = \Illuminate\Support\Str::slug($this->title . '-' . \Illuminate\Support\Facades\Auth::id());
        $this->content_clear = $this->cleanNotes($this->content);

        if($this->blog){
            // validar
            $validatedData = $this->validate();

            // actualizar item en BD
            $this->blog->update($validatedData);

            // agregar tags
            $tagIds = [];
            foreach ($this->selectedBlogTags as $tagName) {
                $tag = \App\Models\Page\Bltag::firstOrCreate(
                    ['name' => $tagName],
                    [
                        'slug' => \Illuminate\Support\Str::slug($tagName),
                        'uuid' => \Illuminate\Support\Str::random(24),
                        'user_id' => \Illuminate\Support\Facades\Auth::id(),
                    ]
                );

                $tagIds[] = $tag->id;
            }
            $this->blog->tags()->sync($tagIds);

            // mensaje de success
            session()->flash('success', 'Editado correctamente');

        }else{
            // datos automaticos
            $this->user_id = \Illuminate\Support\Facades\Auth::id();
            $this->uuid = \Illuminate\Support\Str::random(24);

            // validar
            $validatedData = $this->validate();

            // crear en BD
            $s = Blog::create($validatedData);

            // agregar tags
            $tagIds = [];
            foreach ($this->selectedBlogTags as $tagName) {
                $tag = \App\Models\Page\Bltag::firstOrCreate(
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
        $this->redirectRoute('blogs.index', navigate:true);
    }
};
?>

<div>
    {{-- titulo, descripcion y breadcrumbs --}}
    <div>
        <div class="mb-1 space-y-1">
            <flux:heading size="xl" level="1">
                <a href="{{ route('blogs.index') }}"><flux:button size="xs" variant="ghost" icon="arrow-uturn-left"></flux:button></a>
                {{ $this->titlePage }}
            </flux:heading>
            <flux:text class="text-base">{{ $this->subtitlePage }}</flux:text>
    
            <flux:breadcrumbs>
                <flux:breadcrumbs.item href="{{ route('dashboard') }}">Dashboard</flux:breadcrumbs.item>
                <flux:breadcrumbs.item href="{{ route('blogs.index') }}">Blogs</flux:breadcrumbs.item>
                <flux:breadcrumbs.item>{{ $this->titlePage }}</flux:breadcrumbs.item>
            </flux:breadcrumbs>
    
            <flux:separator variant="subtle" />
        </div>
    </div>

    {{-- formulario completo --}}
    <div class="space-y-2">

        <flux:input type="text" label="Titulo" wire:model="title" placeholder="Titulo del blog" autofocus/>
        
        <flux:textarea
            label="Descripcion"
            placeholder="Coloque una breve descripcion del blog"
            wire:model="excerpt"
            rows="5"
        />
        
        <flux:label>Contenido</flux:label>
        <x-libraries.quill-textarea-form 
            id_quill="editor_create_content" 
            name="content"
            height="600" 
            placeholder="{{ __('Contenido') }}" model="content"
            model_data="{{ $content }}" 
        />

        <div class="grid grid-cols-2 gap-1">
            <flux:input type="text" label="Link de imagen" wire:model="cover_image_url" placeholder="Pegue el link de una imagen"/>
            
            <flux:select wire:model="type" label="Tipo">
                <option value="">Seleccionar tipo</option>
                @foreach ($this->types() as $key => $item)
                <option value="{{ $key }}">{{ $item }}</option>
                @endforeach
            </flux:select>
        </div>

        <flux:label>Etiquetas</flux:label>
        <flux:input.group>
            <flux:input type="text" wire:model="newTag" wire:keydown.period.prevent="addTag('selectedBlogTags')" placeholder="Agregue etiquetas" />
            <flux:button wire:click="addTag('selectedBlogTags')" icon="plus">Agregar</flux:button>
        </flux:input.group>

        <div class="flex gap-2 mt-2">
            @foreach($selectedBlogTags as $index => $tag)
                <flux:badge size="sm" color="purple">
                    <button class="mr-2" wire:click="removeTag('selectedBlogTags', {{ $index }})">
                        x
                    </button>
                    #{{ $tag }}
                </flux:badge>
            @endforeach
        </div>

        <x-libraries.utilities.errors />

        <flux:button :icon="$blog ? 'pencil-square' : 'plus'" wire:click="updateItem">{{ $this->buttonSubmit }}</flux:button>
    </div>
</div>