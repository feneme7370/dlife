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

    use \App\Traits\SortTitle;
    use \App\Traits\QueryStrings;
    
        
    //////////////////////////////////////////////////////////////////// PRE CARGAR DATOS
        public function mount(){
        $this->sortFieldSelected('day');
        $this->dayStart = \Carbon\Carbon::parse('1900-01-01')->format('Y-m-d');
        $this->dayEnd = \Carbon\Carbon::parse('2200-12-31')->format('Y-m-d');
        $this->diariesQuery();
        $this->highlightedDays = $this->getDays();
    }

    //////////////////////////////////////////////////////////////////// PROPIEDADES
    // propiedades de item y titulos
    public $diaries;
    public $titlePage = 'Diario';
    public $subtitlePage = 'Listado de dias';

    public $tag_selected, $category_selected;

    //////////////////////////////////////////////////////////////////// CONSULTA DE LISTADO Y ELIMINAR ITEM
    protected function diariesQuery(){
        $this->diaries = Diary::where('user_id', Auth::id())
            ->where(function ($query) {
                $query->where('title', 'like', "%{$this->search}%")
                      ->orWhere('content', 'like', "%{$this->search}%");
            })
            ->when($this->tag_selected, function ($query) {
                $query->whereHas('tags', function ($q) {
                    $q->where('tags.uuid', $this->tag_selected);
                });
            })
            ->when($this->category_selected, function ($query) {
                $query->whereHas('categories', function ($q) {
                    $q->where('categories.uuid', $this->category_selected);
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
        $this->validate([
            'title_template' => 'required|string|max:255',
            'content_template' => 'required|string',
        ]);
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
        $this->dayEnd = \Carbon\Carbon::parse('2200-12-31')->format('Y-m-d');
        $this->search = '';
        $this->category_selected = '';
        $this->tag_selected = '';
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
    <x-page.partials.title-page 
        :title="$this->titlePage"
        :create-route="'diaries.create'"
        :breadcrumbs="[
            ['label' => 'Dashboard', 'route' => 'dashboard'],
            ['label' => $this->titlePage]
        ]"
    />

     {{-- toast de mensaje --}}
     <x-libraries.flux.toast-success />

    <div class="mt-1 flex items-center gap-1">

        <x-page.diaries.modals.add-template />
        
        @foreach ($this->getDiaryTemplates() as $item)
            <flux:badge size="sm" class="hover:cursor-pointer" color="violet">
                <a><flux:button size="xs" variant="ghost" icon="trash" wire:confirm="Quiere eliminar?" wire:click="deleteTemplate('{{ $item->uuid }}')"></flux:button></a>
                <a href="{{ route('diaries.create', ['templateUuid' => $item->uuid]) }}">
                    <span class="ml-1">{{ $item->title }}</span>
                </a>
            </flux:badge>
        @endforeach
        
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
            {{-- barra de busqueda --}}
            <x-page.partials.input-search />
            
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
                                        @foreach ($item->categories as $cat)
                                            <a href="{{ route('diaries.index', ['cat' => $cat->uuid]) }}">
                                                <flux:badge size="sm" color="lime">{{ $cat->name }}</flux:badge>
                                            </a>
                                        @endforeach
                                    </p>
                                    <p>
                                        @foreach ($item->tags as $tag)
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

    {{-- links para pendientes y estadisticas --}}
    <flux:badge color="yellow"><a href="{{ route('diaries.all', ['s' => $dayStart, 'e' => $dayEnd]) }}">Listado</a></flux:badge>

    {{-- exportacion e importacion de excel --}}
    <livewire:pages::page.partials.export-excel-complete 
        table_export="Diary"
        table_import="Diary"
        name_file_export="diaries"
        route_redirect_after_import="diaries.index"
    />
</div>