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
    public string $titlePage = 'Editar nota del dia';
    public string $subtitlePage = 'Edite una nota del dia a la lista';

    // propiedades del item
    public string $title = '';
    public string $content = '';
    public string $content_clear = '';
    public $day;
    public int $status = 0;
    public string $uuid = '';
    public int $user_id = 0;

    public $diary;

    // propiedades para relacion muchos a muchos
    public $selectedDiaryCategories = [];
    public $selectedDiaryDtags = [];

    //////////////////////////////////////////////////////////////////// VALIDACIONES
    // reglas de validacion
    protected function rules(){
        return [
            'title' => ['required', 'string', 'max:255'],
            'content' => ['nullable', 'string'],
            'content_clear' => ['nullable', 'string'],
            'day' => ['nullable', 'date'],
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
    public function mount($diaryUuid){
        $this->diary = Diary::where('uuid', $diaryUuid)->first();

        $this->title = $this->diary->title ?? '';
        $this->content = $this->diary->content ?? '';
        $this->content_clear = $this->diary->content_clear ?? '';
        $this->day = \Carbon\Carbon::parse($this->diary->day)->format('Y-m-d') ?? \Carbon\Carbon::now()->format('Y-m-d');
        $this->status = $this->diary->status ?? 0;
        $this->uuid = $this->diary->uuid ?? '';
        $this->user_id = $this->diary->user_id ?? 0;

        $this->selectedDiaryCategories = $this->diary->diary_dcategories->pluck('id')->toArray() ?? [];
        $this->selectedDiaryDtags = $this->diary->diary_dtags->pluck('name')->toArray() ?? [];
    }

    //////////////////////////////////////////////////////////////////// DATOS PARA ASOCIAR
    // traer estados
    public function diary_status(){
        return Diary::humor_status();
    }

    // traer datos de generos para asociar
    public function diary_categories(){
        return Dcategory::where('user_id', \Illuminate\Support\Facades\Auth::id())
            ->orderBy('name', 'asc')
            ->get();
    }
    
    //////////////////////////////////////////////////////////////////// STORE PARA EDITAR
    // crear item en la BD
    public function updateItem(){
        $this->content_clear = $this->cleanNotes($this->content);
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

        // mensaje de success
        session()->flash('success', 'Editado correctamente');

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
                @foreach ($this->diary_status() as $key => $item)
                    <option value="{{ $key }}">{{ $item }}</option>
                @endforeach
            </flux:select>
        </div>

        <x-libraries.quill-textarea-form 
        id_quill="editor_create_content" 
        name="content"
        rows="15" 
        placeholder="{{ __('Descripcion') }}" model="content"
        model_data="{{ $content }}" 
        />

        <div class="flex items-center gap-1">
            <flux:label>Categorias {{ count($selectedDiaryCategories) }}</flux:label>
        </div>
        <flux:checkbox.group wire:model.live="selectedDiaryCategories">
            <div class="h-40 overflow-scroll space-y-1">
                @foreach ($this->diary_categories() as $item)
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

        <flux:button icon="pencil-square" wire:click="updateItem">Editar</flux:button>
    </div>
</div>