<?php

use App\Models\Page\Subject;
use Livewire\Component;

new class extends Component
{
    //////////////////////////////////////////////////////////////////// PROPIEDADES
    //propiedades de titulos
    public string $titlePage = 'Ver sujeto';
    public string $subtitlePage = 'Ver sujetos de lista';

    // propiedades del item
    public string $name = '';
    public string $slug = '';
    public string $country = '';
    public string $birthdate = '';
    public string $description = '';
    public string $cover_image_url = '';
    public string $uuid = '';
    public int $user_id = 0;

    //////////////////////////////////////////////////////////////////// PRE CARGAR DATOS
    // precargar datos al iniciar pagina
    public function mount($subjectUuid){
        $item = Subject::where('uuid', $subjectUuid)->first();
        $this->name = $item->name ?? '';
        $this->slug = $item->slug ?? '';
        $this->country = $item->country ?? '';
        $this->birthdate = $item->birthdate ?? '';
        $this->description = $item->description ?? '';
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
            ['label' => 'Sujetos', 'route' => 'subjects.index'],
            ['label' => $this->titlePage]
        ]"
    />

    {{-- ver datos del item --}}
    <div class="flex flex-col sm:flex-row gap-2">
        <img src="{{ $this->cover_image_url }}" class="w-72 h-72 bg-cover rounded-sm" alt="">
        <div>
            <p class="text-xl font-bold">{{ $this->name }}</p>
            <p class="text-sm italic">{{ $this->country }} - {{ \Carbon\Carbon::parse($this->birthdate)->format('d-m-Y') }}</p>
            <p class="mt-3">{{ $this->description }}</p>
        </div>
    </div>
</div>