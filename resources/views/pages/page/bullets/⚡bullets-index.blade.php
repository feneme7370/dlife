<?php

use App\Models\Page\Blog;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

new class extends Component
{
    use WithFileUploads;
    use WithPagination;

    //////////////////////////////////////////////////////////////////// PROPIEDADES DE PAGINACION
    // propiedades para paginacion y orden, actualizar al buscar
    public $search = '', $sortField = 'year', $sortDirection = 'desc', $perPage = 10000;
    public function updatingSearch(){$this->resetPage();}
    public function updatedSearch(){$this->queryBujos();}
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
        // opcional: setear año actual si existe
        
        $this->types = Blog::bullet_types();
        $this->bujos = $this->queryBujos();
        
        // traer todos los años con bujos
        $this->bujos_years = $this->bujos
        ->pluck('year')
        ->unique()
        ->sortDesc()
        ->values();
        
        $this->year_selected = now()->year;

        // 🔥 sacás los años desde la misma colección

    }
    //////////////////////////////////////////////////////////////////// PROPIEDADES
    // propiedades de item y titulos
    public $titlePage = 'Bullet Journal';
    public $subtitlePage = 'Listado de bullets';
    public $types = [];
    public $year_selected;
    public $bujos_years;
    public $bujos;

    //////////////////////////////////////////////////////////////////// FILTRO DE AÑO
    // chage new year filter
    public function newYear($value){
        if($value === 'todo'){
            $this->year_selected = '';
        }else{
            $this->year_selected = $value;
        }
    }

    //////////////////////////////////////////////////////////////////// CONSULTA DE LISTADO Y ELIMINAR ITEM
    // consulta de item
    public function queryBujos(){
        return Blog::where('user_id', Auth::id())
            ->where('entry_type', 'bullet')
            ->where(function ($query) {
                $query->where('title', 'like', "%{$this->search}%")
                      ->orWhere('slug', 'like', "%{$this->search}%");
            })
            ->orderBy('year', 'desc') // 🔥 importante
            ->orderBy('type', 'asc')
            ->get();
    }

    // filtrar de propiedad total solo por año seleccionado
    public function filter_bujos(){
        return $this->queryBujos()
            ->when($this->year_selected != '', function( $query) {
            return $query->where('year', $this->year_selected);
            })
            ->groupBy([
                fn($item) => $item->year ?? 'Sin año',
                fn($item) => $item->type ?? 'sin_tipo'
            ]);
    }
    // eliminar item
    public function deleteItem($uuid){
        $item = Blog::where('user_id', Auth::id())->where('uuid', $uuid)->first();
        $item->delete();
    }
};
?>

<div>
    {{-- titulo, descripcion y breadcrumbs --}}
    <x-page.partials.title-page 
        :title="$this->titlePage"
        :create-route="'bullets.create'"
        :breadcrumbs="[
            ['label' => 'Dashboard', 'route' => 'dashboard'],
            ['label' => $this->titlePage]
        ]"
    />

    {{-- toast de mensaje --}}
    <x-libraries.flux.toast-success />

    <div class="flex items-center gap-1">
        {{-- barra de busqueda --}}
        <x-page.partials.input-search />
    
        {{-- filtro por año --}}
        <flux:dropdown class="mt-3">
            <flux:button class="text-center" icon:trailing="chevron-down">{{ $this->year_selected == 1900 ? 'Todos' : $this->year_selected }}</flux:button>
            
            <flux:menu>
                <flux:menu.radio.group>
                    <flux:menu.radio wire:navigated wire:click="newYear('todo')">Todos</flux:menu.radio>
                    @foreach ($this->bujos_years as $read_year)
                        <flux:menu.radio wire:click="newYear({{ $read_year }})">{{ $read_year }}</flux:menu.radio>
                    @endforeach
                </flux:menu.radio.group>
            </flux:menu>
        </flux:dropdown>
    </div>

    {{-- listado de sagas --}}
    <div class="space-y-2">
        @foreach ($this->filter_bujos() as $year => $categories_types)

            <flux:separator text="{{ $year }}" />

            @foreach ($categories_types as $category_type => $bullets)

                <p class="font-bold">{{ $this->types[$category_type] ?? '' }}</p>

                @foreach ($bullets as $item)
                    <div class="flex items-center justify-between">

                        <a class="hover:underline ml-4 sm:ml-5" href="{{ route('bullets.show', ['bulletUuid' => $item->uuid]) }}">- {{ $item->title }}</a>

                        <div class="flex items-center justify-center">
                                <a href="{{ route('bullets.edit', ['bulletUuid' => $item->uuid]) }}"><flux:button size="xs" variant="ghost" icon="pencil-square"></flux:button></a>
                                <a><flux:button size="xs" variant="ghost" icon="trash" wire:confirm="Quiere eliminar?" wire:click="deleteItem('{{ $item->uuid }}')"></flux:button></a>
                        </div>

                    </div>
                @endforeach
            @endforeach
        @endforeach
    </div>

    {{-- paginacion --}}
    {{-- <div class="mt-3">
        {{ $this->queryBullets()->links() }}
    </div> --}}

    {{-- exportacion e importacion de excel --}}
    <livewire:pages::page.partials.export-excel-complete 
        table_export="Blogs"
        table_import="Blogs"
        name_file_export="blogsandbullets"
        route_redirect_after_import="bullets.index"
    />

</div>