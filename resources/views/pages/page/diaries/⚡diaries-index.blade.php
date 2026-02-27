<?php

use App\Models\Page\Diary;
use App\Models\Page\DiaryTemplate;
use App\Models\Page\Subject;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Livewire\Attributes\On;

new class extends Component
{
    use WithFileUploads;
    use WithPagination;


    // propiedades para paginacion y orden, actualizar al buscar
    public $search = '', $sortField = 'day', $sortDirection = 'asc', $perPage = 10000;
    public function updatingSearch(){$this->resetPage();}

    // funcion para ordenar la tabla
    public function sortBy($field){
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortDirection = 'asc';
        }
        $this->sortField = $field;
    }

    // propiedades de item y titulos
    public $file;
    public $diaries;
    public $title = 'Diario';
    public $subtitle = 'Listado de dias';

    // consulta de item
    public function queryDiaries(){
        return Diary::where('user_id', Auth::id())
            ->whereBetween('day', [$this->startOfMonth(), $this->endOfMonth()])
            ->where(function ($query) {
                $query->where('title', 'like', "%{$this->search}%")
                      ->orWhere('content', 'like', "%{$this->search}%");
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->get();
    }

    // eliminar item
    public function deleteItem($codigo){
        $item = Diary::where('user_id', Auth::id())->where('uuid', $codigo)->first();
        $item->delete();
    }

    // eliminar template
    public function deleteTemplate($codigo){
        $item = DiaryTemplate::where('user_id', Auth::id())->where('uuid', $codigo)->first();
        $item->delete();
    }
    
    public $currentMonth;
    public $date;
    
    public function mount(){
        $this->currentMonth = \Carbon\Carbon::now()->format('Y-m');
        $this->date = \Carbon\Carbon::parse($this->currentMonth . '-01')->format('Y-m-d');
    }

    public function startOfMonth(){
        return \Carbon\Carbon::parse($this->date)->copy()->startOfMonth();
    }
    public function endOfMonth(){
        return \Carbon\Carbon::parse($this->date)->copy()->endOfMonth();
    }
    public function daysInMonth(){
        return \Carbon\Carbon::parse($this->date)->daysInMonth;
    }
    public function getDays(){
        return $this->queryDiaries()
            ->pluck('day')
            ->map(fn ($d) => \Carbon\Carbon::parse($d)->format('Y-m-d'))
            ->toArray();
    }


    // exportar tabla cruda a excel
    public function export($table)
    {
        $data = \Illuminate\Support\Facades\DB::table($table)->where('user_id', Auth::id())->get();

        return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\GenericExport($data, $table),"{$table}.xlsx");
    }

    // importar tabla cruda de excel
    public function import()
    {
        $this->validate([
            'file' => 'required|mimes:xlsx,csv'
        ]);

        \Maatwebsite\Excel\Facades\Excel::import(new \App\Imports\DailyLogImport(), $this->file);

        $this->reset('file');

        session()->flash('success', 'Importación exitosa');
    }

    public function getDiaryTemplates(){
        return \App\Models\Page\DiaryTemplate::where('user_id', Auth::id())->get();
    }
    public $title_template;
    public $content_template;
    public function addTemplate(){
        \App\Models\Page\DiaryTemplate::create([
            'title' => $this->title_template,
            'content' => $this->content_template,
            'user_id' => \Illuminate\Support\Facades\Auth::id(),
            'uuid' => \Illuminate\Support\Str::random(24),
        ]);
        $this->getDiaryTemplates();
        $this->modal('add-template')->close();
    }
};
?>

<div>
    {{-- titulo, descripcion y breadcrumbs --}}
    <div>
        <flux:main container class="mb-1 space-y-1">
            <flux:heading size="xl" level="1">
                <a href="{{ route('diaries.create') }}"><flux:button size="xs" variant="ghost" icon="plus"></flux:button></a>
                {{ $this->title }}
            </flux:heading>
            <flux:text class="text-base">{{ $this->subtitle }}</flux:text>
    
            <flux:breadcrumbs>
                <flux:breadcrumbs.item href="{{ route('dashboard') }}">Dashboard</flux:breadcrumbs.item>
                <flux:breadcrumbs.item>{{ $this->title }}</flux:breadcrumbs.item>
            </flux:breadcrumbs>
    
            <flux:separator variant="subtle" />

            {{-- links para pendientes y estadisticas --}}
            <div class="mt-1 flex items-center gap-1">
                <flux:modal.trigger name="add-template">
                    <flux:button variant="ghost" icon="plus"></flux:button>
                </flux:modal.trigger>

                @foreach ($this->getDiaryTemplates() as $item)
                    <flux:badge class="hover:cursor-pointer" color="violet">
                        <a><flux:button size="xs" variant="ghost" icon="trash" wire:confirm="Quiere eliminar?" wire:click="deleteTemplate('{{ $item->uuid }}')"></flux:button></a>
                        <a href="{{ route('diaries.create', ['templateUuid' => $item->uuid]) }}">
                            <span class="ml-1">{{ $item->title }}</span>
                        </a>
                    </flux:badge>
                @endforeach
            </div>
        </flux:main>
    </div>


    <div class="grid sm:grid-cols-2">
        <div class="mx-auto space-y-3">
            {{-- Header navegación --}}
            <div>
                <flux:input type="date" label="{{ \Carbon\Carbon::parse($this->date)->format('Y-m') }}" wire:model.live="date"/>
            </div>
    
            {{-- Calendario --}}
            <div class="grid grid-cols-7 gap-2 text-center">
                @for ($i = 1; $i <= $this->daysInMonth(); $i++)
                    @php
                        $fullDate = \Carbon\Carbon::parse($this->date)->copy()->day($i)->format('Y-m-d');
                        $hasEntry = in_array($fullDate, $this->getDays());
                    @endphp
        
                    <div class="p-2 rounded-lg border text-gray-800
                        {{ $hasEntry ? 'bg-green-200 font-bold' : 'bg-gray-100' }}">
                        <a href="#">{{ $i }}</a>
                    </div>
                @endfor
            </div>
        </div>
    
        {{-- buscador --}}
        {{-- <div class="mb-3">
            <flux:input type="text" label="Buscar" wire:model.live.debounce.250ms="search" placeholder="Buscar" autofocus/>
        </div> --}}
    
        {{-- listado de sujetos --}}
        <div class="space-y-2 mt-2 h-96 overflow-scroll">
            <flux:label>Notas</flux:label>
            @foreach ($this->queryDiaries() as $item)
                <div class="flex items-center justify-between">
                    <p class="text-xs italic font-light"><a class="hover:underline" href="{{ route('diaries.show', ['diaryUuid' => $item->uuid]) }}">{{ \Carbon\Carbon::parse($item->day)->format('d-m-Y') }} - <span class="text-sm">{{ $item->title }}</span></p></a>
    
                    <div class="flex items-center justify-center">
                            <a href="{{ route('diaries.edit', ['diaryUuid' => $item->uuid]) }}"><flux:button size="xs" variant="ghost" icon="pencil-square"></flux:button></a>
                            <a><flux:button size="xs" variant="ghost" icon="trash" wire:confirm="Quiere eliminar?" wire:click="deleteItem('{{ $item->uuid }}')"></flux:button></a>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- exportacion e importacion de excel --}}
    <flux:separator class="mb-2 mt-10" variant="subtle" />
    <div class="flex justify-between items-center gap-1">
        <flux:button icon="cloud-arrow-down" class="text-xs text-center" wire:click="export('diaries')">Exp.</flux:button>
        <div class="flex gap-3">
            <flux:button icon="cloud-arrow-up" class="text-xs text-center" wire:click="import()">Imp.</flux:button>
            <flux:input type="file" wire:model="file" />
        </div>
    </div>
    <flux:modal name="add-template" class="md:w-96">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Agregue una plantilla</flux:heading>
                <flux:text class="mt-2">Agregue una nueva plantilla para su nota.</flux:text>
            </div>

            <flux:input wire:model="title_template" label="Titulo" placeholder="Titulo de la plantilla" />

            <flux:textarea
                label="Contenido"
                placeholder="Coloque el contenido"
                wire:model="content_template"
                rows="10"
            />

            <div class="flex">
                <flux:spacer />

                <flux:button wire:click="addTemplate" variant="primary">Agregar</flux:button>
            </div>
        </div>
    </flux:modal>
</div>