<?php

use App\Models\Page\Subject;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

new class extends Component
{
    use WithFileUploads;
    use WithPagination;

    // propiedades para paginacion y orden, actualizar al buscar
    public $search = '', $sortField = 'name', $sortDirection = 'asc', $perPage = 10000;
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
    public $subjects;
    public $title = 'Sujetos';
    public $subtitle = 'Listado de sujetos';

    // consulta de item
    public function querySubjects(){
        return Subject::where('user_id', Auth::id())
            ->where(function ($query) {
                $query->where('name', 'like', "%{$this->search}%")
                      ->orWhere('slug', 'like', "%{$this->search}%");
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);
    }

    // eliminar item
    public function deleteItem($codigo){
        $item = Subject::where('user_id', Auth::id())->where('uuid', $codigo)->first();
        $item->delete();
    }

    // exportar tabla cruda a excel
    public function export($table)
    {
        $data = \Illuminate\Support\Facades\DB::table($table)->where('user_id', Auth::id())->get();

        return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\GenericExport($data, $table),"{$table}.xlsx");
    }

    // importar tabla cruda de excel
    public function import($table)
    {
        $this->validate([
            'file' => 'required|mimes:xlsx,csv'
        ]);

        \Maatwebsite\Excel\Facades\Excel::import(new \App\Imports\GenericImport($table), $this->file);

        $this->reset('file');

        session()->flash('success', 'Importación exitosa');
    }
};
?>

<div>
    {{-- titulo, descripcion y breadcrumbs --}}
    <div>
        <flux:main container class="mb-1 space-y-1">
            <flux:heading size="xl" level="1">
                <a href="{{ route('subjects.create') }}"><flux:button size="xs" variant="ghost" icon="plus"></flux:button></a>
                {{ $this->title }}
            </flux:heading>
            <flux:text class="text-base">{{ $this->subtitle }}</flux:text>
    
            <flux:breadcrumbs>
                <flux:breadcrumbs.item href="{{ route('dashboard') }}">Dashboard</flux:breadcrumbs.item>
                <flux:breadcrumbs.item>{{ $this->title }}</flux:breadcrumbs.item>
            </flux:breadcrumbs>
    
            <flux:separator variant="subtle" />
        </flux:main>
    </div>

    {{-- buscador --}}
    <div class="mb-3">
        <flux:input type="text" label="Buscar" wire:model.live.debounce.250ms="search" placeholder="Buscar" autofocus/>
    </div>

    {{-- listado de sujetos --}}
    <div class="space-y-2">
        @foreach ($this->querySubjects() as $item)
            <div class="flex items-center justify-between">

                <div class="flex items-center gap-3">
                    <img src="{{ $item->cover_image_url }}" class="w-8 h-8 bg-cover rounded-sm" alt="">
                    <p><a class="hover:underline" href="{{ route('subjects.show', ['subjectUuid' => $item->uuid]) }}">{{ $item->name }}</p></a>
                    <p class="text-xs italic text-gray-600">{{ $item->country }} - {{ $item->birthdate }}</p>
                </div>

                <div class="flex items-center justify-center">
                        <a href="{{ route('subjects.edit', ['subjectUuid' => $item->uuid]) }}"><flux:button size="xs" variant="ghost" icon="pencil-square"></flux:button></a>
                        <a><flux:button size="xs" variant="ghost" icon="trash" wire:confirm="Quiere eliminar?" wire:click="deleteItem('{{ $item->uuid }}')"></flux:button></a>
                </div>

            </div>
        @endforeach
    </div>

    {{-- paginacion --}}
    <div class="mt-3">
        {{ $this->querySubjects()->links() }}
    </div>

    {{-- exportacion e importacion de excel --}}
    <flux:separator class="mb-2 mt-10" variant="subtle" />
    <div class="flex justify-between items-center gap-1">
        <flux:button icon="cloud-arrow-down" class="text-xs text-center" wire:click="export('subjects')">Exp.</flux:button>
        <div class="flex gap-3">
            <flux:button icon="cloud-arrow-up" class="text-xs text-center" wire:click="import('subjects')">Imp.</flux:button>
            <flux:input type="file" wire:model="file" />
        </div>
    </div>

</div>