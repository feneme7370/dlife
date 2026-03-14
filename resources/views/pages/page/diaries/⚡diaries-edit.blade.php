<?php

use App\Models\Page\Dcategory;
use App\Models\Page\Diary;
use Livewire\Component;

new class extends Component
{
    use \App\Traits\HandlesTags;
    use \App\Traits\CleansHtml;

    //////////////////////////////////////////////////////////////////// PROPIEDADES PRINCIPALES
    //propiedades de titulos
    public string $titlePage = '';
    public string $subtitlePage = '';
    public string $buttonSubmit = '';

    // propiedades del item
    public string $title = '';
    public string $content = '';
    public string $content_clear = '';
    public $day = '';
    public int $status = 0;
    public string $uuid = '';
    public int $user_id = 0;

    public $diary;

    // propiedades para asociar
    public $diary_status = [];
    public $diary_categories = [];

    // propiedades para relacion muchos a muchos
    public $selectedDiaryCategories = [];
    public $selectedDiaryDtags = [];

    //////////////////////////////////////////////////////////////////// VALIDACIONES
    // reglas de validacion
    protected function rules(){
        return [
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'content_clear' => ['required', 'string'],
            'day' => ['required', 'date'],
            'status' => ['required', 'integer', 'min:0', 'max:10'],
            'uuid' => ['required', 'string', 'max:255', \Illuminate\Validation\Rule::unique('diaries', 'uuid')->ignore($this->diary?->id ?? 0)],
            'user_id' => ['required', 'exists:users,id'],
        ];
    }

    // renombrar variables a castellano
    protected $validationAttributes = [
        'title' => 'titulo',
        'content' => 'contenido',
        'content_clear' => 'contenido limpio',
        'day' => 'dia',
        'status' => 'estado',
        'uuid' => 'uuid',
        'user_id' => 'usuario',
    ];

    //////////////////////////////////////////////////////////////////// PRE CARGAR DATOS
    // traer datos iniciales
    public function mount($diaryUuid = null, $templateUuid = ''){
        $this->diary = Diary::where('uuid', $diaryUuid)->first();
        $this->diary_status = Diary::humor_status();
        $this->diary_categories = Dcategory::where('user_id', \Illuminate\Support\Facades\Auth::id())
            ->orderBy('name', 'asc')
            ->get();

        // titulos y textos dependiendo si se encuentra el item o no
        $this->titlePage = $this->diary ? 'Modificar frase' : 'Agregar frase';
        $this->subtitlePage = $this->diary ? 'Modificar datos del frase' : 'Agregar datos del frase';
        $this->buttonSubmit = $this->diary ? 'Modificar' : 'Agregar';

        // si se encuentra el item, cargar datos para editar, sino cargar datos para crear nuevo item
        if($this->diary){      
            // cargar datos del item a editar      
            $this->title = $this->diary->title ?? '';
            $this->content = $this->diary->content ?? '';
            $this->content_clear = $this->diary->content_clear ?? '';
            $this->day = \Carbon\Carbon::parse($this->diary->day)->format('Y-m-d') ?? \Carbon\Carbon::now()->format('Y-m-d');
            $this->status = $this->diary->status ?? 0;
            $this->uuid = $this->diary->uuid ?? '';
            $this->user_id = $this->diary->user_id ?? 0;
    
            $this->selectedDiaryCategories = $this->diary->diary_dcategories->pluck('id')->toArray() ?? [];
            $this->selectedDiaryDtags = $this->diary->diary_dtags->pluck('name')->toArray() ?? [];
        }else{
            // datos para crear nuevo item
            $this->day = \Carbon\Carbon::now()->format('Y-m-d');
            $content_template = \App\Models\Page\DiaryTemplate::where('uuid', $templateUuid)->first();
            $this->content = $content_template->content ?? '';
        }
    }
    
    //////////////////////////////////////////////////////////////////// STORE PARA EDITAR
    // crear item en la BD
    public function updateItem(){
        // normalizar
        $this->title = trim($this->title);
        $this->content_clear = $this->cleanNotes($this->content);

        if($this->diary){
            // validar
            $validatedData = $this->validate();

            // crear en BD
            $this->diary->update($validatedData);
            $this->diary->diary_dcategories()->sync($this->selectedDiaryCategories);

            // agregar tags
            $tagIds = [];
            foreach ($this->selectedDiaryDtags as $tagName) {
                $tag = \App\Models\Page\Dtag::firstOrCreate(
                    ['name' => $tagName],
                    [
                        'slug' => \Illuminate\Support\Str::slug($tagName),
                        'uuid' => \Illuminate\Support\Str::random(24),
                        'user_id' => \Illuminate\Support\Facades\Auth::id(),
                    ]
                );

                $tagIds[] = $tag->id;
            }
            $this->diary->diary_dtags()->sync($tagIds);
        }else{
            // datos automaticos
            $this->user_id = \Illuminate\Support\Facades\Auth::id();
            $this->uuid = \Illuminate\Support\Str::random(24);

            // validar
            $validatedData = $this->validate();

            // crear en BD
            $diary = Diary::create($validatedData);
            $diary->diary_dcategories()->sync($this->selectedDiaryCategories);

            // agregar tags
            $tagIds = [];
            foreach ($this->selectedDiaryDtags as $tagName) {
                $tag = \App\Models\Page\Dtag::firstOrCreate(
                    ['name' => $tagName],
                    [
                        'slug' => \Illuminate\Support\Str::slug($tagName),
                        'uuid' => \Illuminate\Support\Str::random(24),
                        'user_id' => \Illuminate\Support\Facades\Auth::id(),
                    ]
                );

                $tagIds[] = $tag->id;
            }
            $diary->diary_dtags()->sync($tagIds);
        }

        // mensaje de success
        session()->flash('success', $this->diary ? 'Editado correctamente' : 'Creado correctamente');

        // redireccionar
        $this->redirectRoute('diaries.index', navigate:true);
    }
};
?>

<div>
    {{-- titulo, descripcion y breadcrumbs --}}
    <div>
        <div class="mb-1 space-y-1">
            <flux:heading size="xl" level="1">
                <a href="{{ route('diaries.index') }}"><flux:button size="xs" variant="ghost" icon="arrow-uturn-left"></flux:button></a>
                {{ $this->titlePage }}
            </flux:heading>
            <flux:text class="text-base">{{ $this->subtitlePage }}</flux:text>
    
            <flux:breadcrumbs>
                <flux:breadcrumbs.item href="{{ route('dashboard') }}">Dashboard</flux:breadcrumbs.item>
                <flux:breadcrumbs.item href="{{ route('diaries.index') }}">Diario</flux:breadcrumbs.item>
                <flux:breadcrumbs.item>{{ $this->titlePage }}</flux:breadcrumbs.item>
            </flux:breadcrumbs>
    
            <flux:separator variant="subtle" />
        </div>
    </div>

    {{-- formulario completo --}}
    <div class="space-y-2">
        <flux:input type="text" label="Titulo" wire:model="title" placeholder="Titulo de la nota" autofocus/>
        <div class="grid grid-cols-2 gap-1">
            <flux:input type="date" max="2999-12-31" label="Dia" wire:model="day"/>
            <flux:select wire:model="status" label="Estado">
                <option value="">Seleccionar humor</option>
                @foreach ($this->diary_status as $key => $item)
                    <option value="{{ $key }}">{{ $item }}</option>
                @endforeach
            </flux:select>
        </div>

        <x-libraries.quill-textarea-form 
        id_quill="editor_create_content" 
        name="content"
        height="500" 
        placeholder="{{ __('Descripcion') }}" model="content"
        model_data="{{ $content }}" 
        />

        <div class="flex items-center gap-1">
            <flux:label>Categorias {{ count($selectedDiaryCategories) }}</flux:label>
        </div>
        <flux:checkbox.group wire:model.live="selectedDiaryCategories">
            <div class="grid grid-cols-2 md:grid-cols-3 h-max-96 overflow-scroll space-y-1">
                @foreach ($this->diary_categories as $item)
                    <flux:checkbox label="{{ $item->name }}" value="{{ $item->id }}" />
                @endforeach
            </div>
        </flux:checkbox.group>

        <flux:label>Etiquetas</flux:label>
        <flux:input.group>
            <flux:input type="text" wire:model="newTag" wire:keydown.period.prevent="addTag('selectedDiaryDtags')" placeholder="Agregue etiquetas" />
            <flux:button wire:click="addTag('selectedDiaryDtags')" icon="plus">Agregar</flux:button>
        </flux:input.group>
        
        <div class="flex gap-2 mt-2">
            @foreach($selectedDiaryDtags as $index => $tag)
                <flux:badge size="sm" color="purple">
                    <button class="mr-2" wire:click="removeTag('selectedDiaryDtags', {{ $index }})">
                        x
                    </button>
                    #{{ $tag }}
                </flux:badge>
            @endforeach
        </div>

        <x-libraries.utilities.errors />

        <flux:button :icon="$diary ? 'pencil-square' : 'plus'" wire:click="updateItem">{{ $this->buttonSubmit }}</flux:button>
    </div>
</div>