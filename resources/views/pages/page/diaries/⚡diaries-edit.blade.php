<?php

use App\Models\Page\Dcategory;
use App\Models\Page\Diary;
use Livewire\Component;

new class extends Component
{
    //propiedades de titulos
    public string $title_diary = 'Editar nota del dia';
    public string $subtitle = 'Edite una nota del dia a la lista';

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
    public $selected_diary_categories = [];

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

        $this->selected_diary_categories = $this->diary->diary_dcategories->pluck('id')->toArray() ?? [];
        $this->selected_diary_dtags = $this->diary->diary_dtags->pluck('name')->toArray() ?? [];
    }

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
    
    // crear item en la BD
    public function updateItem(){
        $this->content_clear = $this->cleanNotes($this->content);
        // validar
        $validatedData = $this->validate();

        // crear en BD
        $this->diary->update($validatedData);
        $this->diary->diary_dcategories()->sync($this->selected_diary_categories);

        // agregar tags
        $tagIds = [];
        foreach ($this->selected_diary_dtags as $tagName) {
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

    // store para crear tags
    public $name_tag;
    public $newTag = '';   // input actual
    public $selected_diary_dtags = [];     // array de tags agregados

    public function addTag()
    {
        $formatted = str_replace(' ', '', \Illuminate\Support\Str::title(trim($this->newTag)));

        if ($formatted && !in_array($formatted, $this->selected_diary_dtags)) {
            $this->selected_diary_dtags[] = $formatted;
        }

        $this->newTag = '';
    }

    public function removeTag($index)
    {
        unset($this->selected_diary_dtags[$index]);
        $this->selected_diary_dtags = array_values($this->selected_diary_dtags); // reindexa
    }
};
?>

<div>
    {{-- titulo, descripcion y breadcrumbs --}}
    <div>
        <div class="mb-1 space-y-1">
            <flux:heading size="xl" level="1">
                <a href="{{ route('diaries.index') }}"><flux:button size="xs" variant="ghost" icon="arrow-uturn-left"></flux:button></a>
                {{ $this->title_diary }}
            </flux:heading>
            <flux:text class="text-base">{{ $this->subtitle }}</flux:text>
    
            <flux:breadcrumbs>
                <flux:breadcrumbs.item href="{{ route('dashboard') }}">Dashboard</flux:breadcrumbs.item>
                <flux:breadcrumbs.item href="{{ route('diaries.index') }}">Diario</flux:breadcrumbs.item>
                <flux:breadcrumbs.item>{{ $this->title_diary }}</flux:breadcrumbs.item>
            </flux:breadcrumbs>
    
            <flux:separator variant="subtle" />
        </div>
    </div>

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

        {{-- <flux:textarea
            label="Descripcion"
            placeholder="Escribir suceso del dia"
            wire:model="content"
            rows="10"
        /> --}}

        <div class="flex items-center gap-1">
            <flux:label>Categorias {{ count($selected_diary_categories) }}</flux:label>
        </div>
        <flux:checkbox.group wire:model.live="selected_diary_categories">
            <div class="h-40 overflow-scroll space-y-1">
                @foreach ($this->diary_categories() as $item)
                    <flux:checkbox label="{{ $item->name }}" value="{{ $item->id }}" />
                @endforeach
            </div>
        </flux:checkbox.group>

        <flux:input type="text" label="Etiquetas" wire:model="newTag" wire:keydown.period.prevent="addTag" placeholder="Agregue etiquetas" />
        <div class="flex gap-2 mt-2">
            @foreach($selected_diary_dtags as $index => $tag)
                <flux:badge size="sm" color="purple">
                    <button class="mr-2" wire:click="removeTag({{ $index }})">
                        x
                    </button>
                    #{{ $tag }}
                </flux:badge>
            @endforeach
        </div>

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