<?php

use App\Models\Page\Collection;
use Livewire\Component;

new class extends Component
{
    //////////////////////////////////////////////////////////////////// PROPIEDADES
    //propiedades de titulos
    public string $titlePage = 'Ver saga';
    public string $subtitlePage = 'Ver sagas de lista';

    // propiedades del item
    public string $name = '';
    public string $slug = '';
    public string $description = '';
    public int $books_amount = 0;
    public int $movies_amount = 0;
    public string $cover_image_url = '';
    public string $uuid = '';
    public int $user_id = 0;

    //////////////////////////////////////////////////////////////////// PRE CARGAR DATOS
    // precargar datos al iniciar pagina
    public function mount($collectionUuid){
        $item = Collection::where('uuid', $collectionUuid)->first();
        $this->name = $item->name ?? '';
        $this->slug = $item->slug ?? '';
        $this->description = $item->description ?? '';
        $this->books_amount = $item->books_amount ?? 0;
        $this->movies_amount = $item->movies_amount ?? 0;
        $this->cover_image_url = $item->cover_image_url ?? '';
        $this->uuid = $item->uuid ?? '';
        $this->user_id = $item->user_id ?? 0;
    }
};
?>

<div>
    {{-- titulo, descripcion y breadcrumbs --}}
    <x-page.partials.title-page 
        :title="$this->titlePage"
        :create-route="'categories.index'"
        icon="arrow-uturn-left"
        :breadcrumbs="[
            ['label' => 'Dashboard', 'route' => 'dashboard'],
            ['label' => 'Asociaciones', 'route' => 'associations.index'],
            ['label' => 'Colecciones', 'route' => 'collections.index'],
            ['label' => $this->titlePage]
        ]"
    />

    {{-- ver datos del item --}}
    <div class="flex flex-col sm:flex-row gap-2">
        <img src="{{ $this->cover_image_url }}" class="w-72 h-72 bg-cover rounded-sm" alt="">
        <div>
            <p class="text-xl font-bold">{{ $this->name }}</p>
            @if ($this->books_amount)
                <p class="mt-1 italic text-xs">{{ $this->books_amount }} libro(s)</p>
            @endif
            @if ($this->movies_amount)
                <p class="mt-1 italic text-xs">{{ $this->movies_amount }} pelicula(s)</p>
            @endif
            <p class="mt-3">{{ $this->description }}</p>
        </div>
    </div>
</div>