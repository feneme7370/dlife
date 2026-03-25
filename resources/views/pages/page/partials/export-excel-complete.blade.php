<?php

use Livewire\Component;

new class extends Component
{
    use \Livewire\WithFileUploads;
    public $file;

    public $table_export;
    public $table_import;
    public $name_file_export;
    public $route_redirect_after_import;

    
    //////////////////////////////////////////////////////////////////// EXPORTAR E IMPORTAR EXCEL
    // exportar tabla cruda a excel
    public function exportComplete(){
        $class = "App\\Exports\\" . $this->table_export. "Export";
        return \Maatwebsite\Excel\Facades\Excel::download(new $class, "{$this->name_file_export}_info.xlsx");
    }
    // importar tabla cruda de excel
    public function importComplete(){
        $this->validate(['file' => 'required|mimes:xlsx,csv']);
        $class = "App\\Imports\\" . $this->table_import. "Import";
        \Maatwebsite\Excel\Facades\Excel::import(new $class, $this->file);
        $this->reset('file');
        session()->flash('success', 'Importación exitosa');

        // redireccionar
        $this->redirectRoute($this->route_redirect_after_import, navigate:true);
    }    
};
?>

<div>
    {{-- exportacion e importacion de excel --}}
    <flux:separator class="mb-2 mt-10" variant="subtle" />

    <div class=" space-y-3">
        <div class="flex justify-between items-center gap-1">
            <flux:button icon="cloud-arrow-down" class="text-xs text-center" wire:click="exportComplete()">Exp.</flux:button>
        </div>
    
        <div class="flex justify-between items-center gap-1">
            <div class="flex gap-1">
                <flux:button icon="cloud-arrow-up" class="text-xs text-center" wire:click="importComplete()">Imp.</flux:button>
                <flux:input type="file" wire:model="file" />
            </div>
        </div>
    </div>
</div>