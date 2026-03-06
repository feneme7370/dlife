<?php

use App\Models\Page\Subject;
use Livewire\Component;

new class extends Component
{
    //propiedades de titulos
    public string $title_diary = 'Ver nota';
    public string $subtitle = 'Ver nota de lista';

    public $diary;

    // traer datos iniciales
    public function mount($diaryUuid){
        $this->diary = \App\Models\Page\Diary::where('uuid', $diaryUuid)->with('diary_dcategories', 'diary_dtags')->first();
    }

    // traer estados
    public function diary_status($item){
        $s = \App\Models\Page\Diary::humor_status();
        return $s[$item];
    }
};
?>

<div>
    {{-- titulo, descripcion y breadcrumbs --}}
    <div>
        <div class="mb-1 space-y-1">
            <flux:heading size="xl" level="1">
                <a href="{{ route('diaries.index') }}"><flux:button size="xs" variant="ghost" icon="arrow-uturn-left"></flux:button></a>
                {{ $this->title_diary }}
            </flux:heading>
            <flux:text class="text-base">{{ $this->subtitle }}</flux:text>
    
            <flux:breadcrumbs>
                <flux:breadcrumbs.item href="{{ route('dashboard') }}">Dashboard</flux:breadcrumbs.item>
                <flux:breadcrumbs.item href="{{ route('diaries.index') }}">Diario</flux:breadcrumbs.item>
                <flux:breadcrumbs.item>{{ $this->title_diary }}</flux:breadcrumbs.item>
            </flux:breadcrumbs>
    
            <flux:separator variant="subtle" />
        </div>
    </div>

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
                @foreach ($this->diary->diary_dcategories as $item)
                    <flux:badge rounded color="amber">
                        <a href="{{ route('diaries.index', ['cat' => $item->uuid]) }}">
                            <span class="text-xs">#{{ $item->name }}</span>
                        </a>
                    </flux:badge>
                @endforeach
            </div>

            <flux:label>Etiquetas</flux:label>
            <div class="my-1">
                @foreach ($this->diary->diary_dtags as $item)
                    <flux:badge rounded color="fuchsia">
                        <a href="{{ route('diaries.index', ['tag' => $item->uuid]) }}">
                            <span class="text-xs">#{{ $item->name }}</span>
                        </a>
                    </flux:badge>
                @endforeach
            </div>
        </div>
    </div>
</div>