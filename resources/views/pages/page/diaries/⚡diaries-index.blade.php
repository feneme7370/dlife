<?php

use App\Models\Page\Diary;
use App\Models\Page\DiaryTemplate;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Livewire\Attributes\On;

new class extends Component
{
    use WithFileUploads;
    use WithPagination;

    //////////////////////////////////////////////////////////////////// PROPIEDADES DE PAGINACION
    // propiedades para paginacion y orden, actualizar al buscar
    public $search = '', $sortField = 'day', $sortDirection = 'desc', $perPage = 10000;
    public function updatingSearch(){$this->resetPage();}
    public function updatedSearch(){$this->diariesQuery();}
    public function updatedDayStart(){$this->diariesQuery();}
    public function updatedDayEnd(){$this->diariesQuery();}
    // funcion para ordenar la tabla
    public function sortBy($field){
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortDirection = 'asc';
        }
        $this->sortField = $field;
    }

    //////////////////////////////////////////////////////////////////// PRE CARGAR DATOS
    public function mount(){
        $this->diariesQuery();
        $this->highlightedDays = $this->getDays();
    }

    //////////////////////////////////////////////////////////////////// FUNCIONES PARA FILTRAR
    // mostrar variables en queryString
    protected function queryString(){
        return [
        'search' => [ 'as' => 'q' ],
        'selectedCategory' => [ 'as' => 'cat' ],
        'selectedTag' => [ 'as' => 'tag' ],
        ];
    }

    //////////////////////////////////////////////////////////////////// PROPIEDADES
    // propiedades de item y titulos
    public $file;
    public $diaries;
    public $titlePage = 'Diario';
    public $subtitlePage = 'Listado de dias';

    public $selectedTag, $selectedCategory;

    //////////////////////////////////////////////////////////////////// CONSULTA DE LISTADO Y ELIMINAR ITEM
    protected function diariesQuery(){
        $this->diaries = Diary::where('user_id', Auth::id())
            ->where(function ($query) {
                $query->where('title', 'like', "%{$this->search}%")
                      ->orWhere('content', 'like', "%{$this->search}%");
            })
            ->when($this->selectedTag, function ($query) {
                $query->whereHas('diary_dtags', function ($q) {
                    $q->where('dtags.uuid', $this->selectedTag);
                });
            })
            ->when($this->selectedCategory, function ($query) {
                $query->whereHas('diary_dcategories', function ($q) {
                    $q->where('dcategories.uuid', $this->selectedCategory);
                });
            })
            ->when($this->dayStart != null, function ($query) {
                $query->whereDate('day', '>=', $this->dayStart);
            })

            ->when($this->dayEnd != null, function ($query) {
                $query->whereDate('day', '<=', $this->dayEnd);
            })

            ->orderBy($this->sortField, $this->sortDirection)
            ->get();

        return $this->diaries;
    }
    // eliminar item
    public function deleteItem($uuid){
        $item = Diary::where('user_id', Auth::id())->where('uuid', $uuid)->first();
        $item->delete();
        $this->diariesQuery();
    }

    //////////////////////////////////////////////////////////////////// CONSULTA DE TEMPLATES
    public $title_template;
    public $content_template;

    public function getDiaryTemplates(){
        return \App\Models\Page\DiaryTemplate::where('user_id', Auth::id())->get();
    }
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
    // eliminar template
    public function deleteTemplate($uuid){
        $item = DiaryTemplate::where('user_id', Auth::id())->where('uuid', $uuid)->first();
        $item->delete();
    }
    
    //////////////////////////////////////////////////////////////////// EXPORTAR E IMPORTAR EXCEL
    // exportar tabla cruda a excel
    public function exportComplete(){
        return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\DailyLogExport,"diaries_info.xlsx");
    }
    // importar tabla cruda de excel
    public function importComplete(){
        $this->validate(['file' => 'required|mimes:xlsx,csv']);
        \Maatwebsite\Excel\Facades\Excel::import(new \App\Imports\DailyLogImport(), $this->file);
        $this->reset('file');
        $this->diariesQuery();
        session()->flash('success', 'Importación exitosa');
    }

    //////////////////////////////////////////////////////////////////// LITEPICKER JS
    // propiedades del item
    public $diaryId;
    public $dayStart, $dayEnd;
    public $highlightedDays = [];

    // clickear un dia del calendario y filtrar ese mismo dia
    #[On('reading-day-selected')]
    public function selectDay($date){
        $this->dayStart = \Carbon\Carbon::parse($date)->format('Y-m-d');
        $this->dayEnd = \Carbon\Carbon::parse($date)->format('Y-m-d');
        $this->diariesQuery();
    }

    // eliminar filtros y traer todo nuevamente
    public function clearDate(){
        $this->dayStart = \Carbon\Carbon::parse('1900-01-01')->format('Y-m-d');
        $this->dayEnd = \Carbon\Carbon::parse('2100-01-01')->format('Y-m-d');
        $this->search = '';
        $this->selectedCategory = '';
        $this->selectedTag = '';
        $this->diariesQuery();
    }

    // obtener los dias con datos
    public function getDays(){
        $s =  $this->diaries
            ->pluck('day')
            ->map(fn ($d) => \Carbon\Carbon::parse($d)->format('Y-m-d'))
            ->toArray();
        return $s;
    }

    //////////////////////////////////////////////////////////////////// ELIMINAR MASIVAMENTE
    // propiedades
    public $selected = [];
    public $selectAll = false;

    // colocar los uuid en el array para luego tener dato a eliminar
    public function updatedSelectAll($value){
        if ($value) {
            $this->selected = $this->diaries->pluck('uuid')->toArray();
        } else {
            $this->selected = [];
        }
    }

    // eliminar datos del array
    public function deleteSelected(){
        \App\Models\Page\Diary::where('user_id', Auth::id())
            ->whereIn('uuid', $this->selected)
            ->delete();

        $this->selected = [];
        $this->selectAll = false;

        $this->diariesQuery();
    }
};
?>
<div>
   
    {{-- titulo, descripcion y breadcrumbs --}}
    <div>
        <div container class="space-y-1">
            <flux:heading size="xl" level="1">
                <a href="{{ route('diaries.create') }}"><flux:button size="xs" variant="ghost" icon="plus"></flux:button></a>
                {{ $this->titlePage }}
            </flux:heading>
            <flux:text class="text-base">{{ $this->subtitlePage }}</flux:text>
    
            <flux:breadcrumbs>
                <flux:breadcrumbs.item href="{{ route('dashboard') }}">Dashboard</flux:breadcrumbs.item>
                <flux:breadcrumbs.item>{{ $this->titlePage }}</flux:breadcrumbs.item>
            </flux:breadcrumbs>
    
            <flux:separator variant="subtle" />

            <div class="mt-1 flex items-center gap-1">
                <flux:modal.trigger name="add-template">
                    <flux:button variant="ghost" icon="plus"></flux:button>
                </flux:modal.trigger>

                @foreach ($this->getDiaryTemplates() as $item)
                    <flux:badge size="sm" class="hover:cursor-pointer" color="violet">
                        <a><flux:button size="xs" variant="ghost" icon="trash" wire:confirm="Quiere eliminar?" wire:click="deleteTemplate('{{ $item->uuid }}')"></flux:button></a>
                        <a href="{{ route('diaries.create', ['templateUuid' => $item->uuid]) }}">
                            <span class="ml-1">{{ $item->title }}</span>
                        </a>
                    </flux:badge>
                @endforeach
                
            </div>
            {{-- links para pendientes y estadisticas --}}
            <flux:badge color="purple"><a href="{{ route('dcategories.index') }}">Categorias</a></flux:badge>
            <flux:badge color="violet"><a href="{{ route('dtags.index') }}">Etiquetas</a></flux:badge>


        </div>
    </div>

    {{-- calendario, buscador y listado --}}
    <div class="grid gap-1 sm:gap-5 md:grid-cols-12">
        {{-- calendar, filtros de fecha y boton de eliminacion --}}
        <div class="md:col-span-4">

            <x-libraries.calendar-litepicker 
                :highlightedDayss="$highlightedDays" 
                id_calendar="diary_calendar"
            />
            
            <div class="flex justify-center items-center gap-1">
                <flux:input size="sm" type="date" label="Inicio" wire:model.live="dayStart"/>
                <flux:input size="sm" type="date" label="Fin" wire:model.live="dayEnd"/>
            </div>

            <div class="flex justify-center items-center gap-3 mt-3">
                <flux:button wire:click='clearDate' size="sm" variant="ghost" color="purple" icon="calendar" size="sm">Limpiar</flux:button>

                <div class="flex flex-col items-center gap-1">
                    <div class="flex items-center gap-1">
                        <input type="checkbox" wire:model.live="selectAll">
                        <span class="text-sm">Seleccionar todos</span>
                    </div>
                    <div>
                        @if(count($selected) > 0)
                            <flux:button 
                                size="xs" 
                                variant="danger"
                                wire:confirm="¿Eliminar seleccionados?"
                                wire:click="deleteSelected"
                            >
                                Eliminar ({{ count($selected) }})
                            </flux:button>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- buscador y listado --}}
        <div class="md:col-span-8">
            {{-- buscador --}}
            <div class="mb-3">
                <flux:input type="text" label="Buscar" wire:model.live.debounce.250ms="search" placeholder="Buscar" autofocus/>
            </div>
            
            {{-- listado de sujetos --}}
            <div class="space-y-1 h-96 overflow-scroll">
                <div class="space-y-1">
                    @foreach ($this->diaries as $item)
                        <div class="flex items-center justify-between py-1">
                            <div class="flex gap-3">
                                <input type="checkbox" wire:model.live="selected" value="{{ $item->uuid }}">
                                <div>
                                    <p class="text-xs italic font-light"><a class="hover:underline" href="{{ route('diaries.show', ['diaryUuid' => $item->uuid]) }}">{{ \Carbon\Carbon::parse($item->day)->format('d-m-Y') }} - <span class="text-sm">{{ $item->title }}</span></p></a>
                                    <p>
                                        @foreach ($item->diary_dcategories as $cat)
                                            <a href="{{ route('diaries.index', ['cat' => $cat->uuid]) }}">
                                                <flux:badge size="sm" color="lime">{{ $cat->name }}</flux:badge>
                                            </a>
                                        @endforeach
                                    </p>
                                    <p>
                                        @foreach ($item->diary_dtags as $tag)
                                            <a href="{{ route('diaries.index', ['tag' => $tag->uuid]) }}">
                                                <span class="text-xs">#{{ $tag->name }}</span>
                                            </a>
                                        @endforeach
                                    </p>
                                </div>
                            </div>
                            
                            <div class="flex items-center justify-center">
                                    <a href="{{ route('diaries.edit', ['diaryUuid' => $item->uuid]) }}"><flux:button size="xs" variant="ghost" icon="pencil-square"></flux:button></a>
                                    <a><flux:button size="xs" variant="ghost" icon="trash" wire:confirm="Quiere eliminar?" wire:click="deleteItem('{{ $item->uuid }}')"></flux:button></a>
                            </div>
                        </div>
                        <flux:spacer class="mx-10 border-gray-300 dark:border-gray-700 border"/>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    {{-- exportacion e importacion de excel --}}
    <flux:separator class="mb-2 mt-10" variant="subtle" />

    <div class="flex justify-between items-center gap-1">
        <flux:button icon="cloud-arrow-down" class="text-xs text-center" wire:click="exportComplete()">Exp.</flux:button>
        <div class="flex gap-3">
            <flux:button icon="cloud-arrow-up" class="text-xs text-center" wire:click="importComplete()">Imp.</flux:button>
            <flux:input type="file" wire:model="file" />
        </div>
    </div>

    {{-- modal para agregar templates --}}
    <flux:modal name="add-template" class="md:w-96">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Agregue una plantilla</flux:heading>
                <flux:text class="mt-2">Agregue una nueva plantilla para su nota.</flux:text>
            </div>

            <flux:input wire:model="title_template" label="Titulo" placeholder="Titulo de la plantilla" />

            <x-libraries.quill-textarea-form 
                id_quill="editor_create_content" 
                name="content_template"
                rows="15" 
                placeholder="{{ __('Descripcion') }}" model="content_template"
            />

            <div class="flex">
                <flux:spacer />

                <flux:button wire:click="addTemplate" variant="primary">Agregar</flux:button>
            </div>
        </div>
    </flux:modal>
</div>