<?php

use App\Models\Page\Subject;
use Livewire\Component;

new class extends Component
{
    //////////////////////////////////////////////////////////////////// PROPIEDADES PRINCIPALES
    //propiedades de titulos
    public string $titlePage = 'Ver nota';
    public string $subtitlePage = 'Ver nota de lista';

    public $diary;

    //////////////////////////////////////////////////////////////////// PRE CARGAR DATOS
    // traer datos iniciales
    public function mount($diaryUuid){
        $this->diary = \App\Models\Page\Diary::where('uuid', $diaryUuid)->with('categories', 'tags')->first();
    }

    //////////////////////////////////////////////////////////////////// CONSULTAR DATOS
    // traer estados
    public function diary_status($item){
        $s = \App\Models\Page\Diary::humor_status();
        return $s[$item];
    }
};
?>

<div>
     {{-- titulo, descripcion y breadcrumbs --}}
    <x-page.partials.title-page 
        :title="$this->titlePage"
        :create-route="'diaries.index'"
        icon="arrow-uturn-left"
        :breadcrumbs="[
            ['label' => 'Dashboard', 'route' => 'dashboard'],
            ['label' => 'Diario', 'route' => 'diaries.index'],
            ['label' => $this->titlePage]
        ]"
    />

     {{-- toast de mensaje --}}
     <x-libraries.flux.toast-success />

    {{-- ver datos del item --}}
    <div class="flex flex-col sm:flex-row gap-2">
        <div class="space-y-3">
            <p class="text-sm">{{ \Carbon\Carbon::parse($this->diary->day)->format('d-m-Y') }} | {{ $this->diary_status($this->diary->status) }}</p>
            <p class="text-xl font-bold">{{ $this->diary->title }}</p>
            <div class="whitespace-pre-wrap break-words">
                <p class="text-sm italic" style="white-space: pre-line">{!! $this->diary->content !!} - </p>
            </div>

            <flux:label>Categorias</flux:label>
            <div class="my-1">
                @foreach ($this->diary->categories as $item)
                    <flux:badge class="m-0.5" rounded color="amber">
                        <a href="{{ route('diaries.index', ['cat' => $item->uuid]) }}">
                            <span class="text-xs">#{{ $item->name }}</span>
                        </a>
                    </flux:badge>
                @endforeach
            </div>

            <flux:label>Etiquetas</flux:label>
            <div class="my-1">
                @foreach ($this->diary->tags as $item)
                    <flux:badge class="m-0.5" rounded color="fuchsia">
                        <a href="{{ route('diaries.index', ['tag' => $item->uuid]) }}">
                            <span class="text-xs">#{{ $item->name }}</span>
                        </a>
                    </flux:badge>
                @endforeach
            </div>
        </div>
    </div>
</div>