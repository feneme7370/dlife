<?php

namespace App\Traits;

trait SortTitle
{
    //////////////////////////////////////////////////////////////////// PROPIEDADES DE PAGINACION
    // propiedades para paginacion y orden, actualizar al buscar
    public $search = '';
    public $sortField = 'title';
    public $sortDirection = 'desc'; 
    public $perPage = 10000;
    
    public function updatingSearch(){$this->resetPage();}
    public function updatingSortField(){$this->resetPage();}
    public function updatingSortDirection(){$this->resetPage();}
    public function updatingPerPage(){$this->resetPage();}

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

    public function sortFieldSelected($field = 'title'){
        return $this->sortField = $field;
    }
}