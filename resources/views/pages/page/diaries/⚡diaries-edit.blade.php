<?php

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
    public string $day = '';
    public int $status = 0;
    public string $uuid = '';
    public int $user_id = 0;

    public $diary;

    // reglas de validacion
    protected function rules(){
        return [
            'title' => ['required', 'string', 'max:255'],
            'content' => ['nullable', 'string'],
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
        $this->day = $this->diary->day ?? \Carbon\Carbon::now()->format('Y-m-d');;
        $this->status = $this->diary->status ?? 0;
        $this->uuid = $this->diary->uuid ?? '';
        $this->user_id = $this->diary->user_id ?? 0;
    }

    // traer estados
    public function diary_status(){
        return Diary::humor_status();
    }

    // crear item en la BD
    public function updateItem(){
        // validar
        $validatedData = $this->validate();

        // crear en BD
        $this->diary->update($validatedData);

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
        <flux:main class="mb-1 space-y-1">
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
        </flux:main>
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
        <flux:textarea
            label="Descripcion"
            placeholder="Escribir suceso del dia"
            wire:model="content"
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