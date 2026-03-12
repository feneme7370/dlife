@props([
    'model', // Nombre del modelo (ej: 'Collection', 'Tag', 'Author')
    'relation', // Relación en el modelo principal (ej: 'collections', 'authors')
    'selected' => [], // Elementos seleccionados (IDs)
    'label' => '', // Etiqueta del campo
    'items' => [], // listado desde consulta
])

@php
    // $items = app("App\\Models\\" . $model)::select('id', 'name')->get()->toArray();
@endphp

<div x-data="{ selected: @entangle($attributes->wire('model')), allItems: @js($items), search: '' }">

    @if ($label)
        <flux:label>{{ $label }}</flux:label>
    @endif
    <flux:input x-model="search" placeholder="Buscar..." />

    <!-- Lista de opciones -->
    <ul class="max-h-40 overflow-y-auto text-sm grid grid-cols-2 md:grid-cols-3 gap-1 my-0.5">
        <template x-for="item in allItems.filter(i => i.name.toLowerCase().includes(search.toLowerCase()))" :key="item.id">
            <li class="cursor-pointer p-1 hover:bg-purple-200 dark:hover:bg-purple-900 rounded-lg"
                @click="selected.includes(item.id) ? selected.splice(selected.indexOf(item.id), 1) : selected.push(item.id)">
                <span x-text="item.name"></span>
                <span x-show="selected.includes(item.id)" class="ml-1 text-green-500 font-bold">✔</span>
            </li>
        </template>
    </ul>

    <!-- Elementos seleccionados -->
    <div class="mt-1 text-sm">
        <template x-for="id in selected" :key="id">
            <flux:badge color="purple" size="sm" class="ml-2">
                <button @click="selected.splice(selected.indexOf(id), 1)" class="cursor-pointer hover:text-gray-400">X</button>
                <span class="mx-0.5"></span>
                <span x-text="allItems.find(i => i.id == id)?.name"></span>
            </flux:badge>
            {{-- <span class="bg-violet-800 text-violet-300 text-xs font-bold me-2 pr-2.5 py-0.5 rounded-lg">
                <button @click="selected.splice(selected.indexOf(id), 1)" class="ml-3 cursor-pointer hover:text-gray-400">X</button>
                <span x-text="allItems.find(i => i.id == id)?.name"></span>
            </span> --}}
        </template>
    </div>
</div>