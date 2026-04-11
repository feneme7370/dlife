<?php
use App\Models\Page\Platform;
use Livewire\Component;

new class extends Component
{
    //////////////////////////////////////////////////////////////////// PROPIEDADES
    //propiedades de titulos
    public string $titlePage = 'Ver plataforma';

    // propiedades del item
    public string $name = '';
    public string $brand = '';
    public string $release_year = '';
    public string $uuid = '';
    public int $user_id = 0;

    //////////////////////////////////////////////////////////////////// PRE CARGAR DATOS
    // precargar datos al iniciar pagina
    public function mount($platformUuid){
        $item = Platform::where('uuid', $platformUuid)->first();
        $this->name = $item->name ?? '';
        $this->brand = $item->brand ?? '';
        $this->release_year = $item->release_year ?? '';

        $this->uuid = $item->uuid ?? '';
        $this->user_id = $item->user_id ?? 0;
    }
};
?>

<div>
    {{-- titulo, descripcion y breadcrumbs --}}
    <x-page.partials.title-page 
        :title="$this->titlePage"
        :create-route="'platforms.index'"
        icon="arrow-uturn-left"
        :breadcrumbs="[
            ['label' => 'Dashboard', 'route' => 'dashboard'],
            ['label' => 'Asociaciones', 'route' => 'associations.index'],
            ['label' => 'Plataformas', 'route' => 'platforms.index'],
            ['label' => $this->titlePage]
        ]"
    />

    {{-- ver datos del item --}}
    <div class="flex flex-col sm:flex-row gap-2">
        <div>
            <p class="text-xl font-bold">{{ $this->name }}</p>
            <p class="mt-3">{{ $this->brand }}</p>
            <p class="mt-3">{{ $this->release_year }}</p>
        </div>
    </div>
</div>