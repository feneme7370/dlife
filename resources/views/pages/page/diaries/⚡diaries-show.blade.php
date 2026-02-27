<?php

use App\Models\Page\Subject;
use Livewire\Component;

new class extends Component
{
    //propiedades de titulos
    public string $title_diary = 'Ver nota';
    public string $subtitle = 'Ver nota de lista';

    // propiedades del item
    public string $title = '';
    public string $content = '';
    public string $day = '';
    public int $status = 0;
    public string $uuid = '';
    public int $user_id = 0;

    public $diary;

    // traer datos iniciales
    public function mount($diaryUuid){
        $this->diary = \App\Models\Page\Diary::where('uuid', $diaryUuid)->first();

        $this->title = $this->diary->title ?? '';
        $this->content = $this->diary->content ?? '';
        $this->day = $this->diary->day ?? \Carbon\Carbon::now()->format('Y-m-d');;
        $this->status = $this->diary->status ?? 0;
        $this->uuid = $this->diary->uuid ?? '';
        $this->user_id = $this->diary->user_id ?? 0;
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
        <flux:main class="mb-1 space-y-1">
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
        </flux:main>
    </div>

    {{-- ver datos del item --}}
    <div class="flex flex-col sm:flex-row gap-2">
        <div class="space-y-3">
            <p class="text-sm">{{ \Carbon\Carbon::parse($this->day)->format('d-m-Y') }} | {{ $this->diary_status($this->status) }}</p>
            <p class="text-xl font-bold">{{ $this->title }}</p>
            <p class="text-sm italic" style="white-space: pre-line">{{ $this->content }} - </p>
        </div>
    </div>
</div>