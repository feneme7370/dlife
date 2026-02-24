<?php

use App\Models\Page\Subject;
use Livewire\Component;

new class extends Component
{
    //propiedades de titulos
    public string $title = 'Ver sujeto';
    public string $subtitle = 'Ver sujetos de lista';

    // propiedades del item
    public string $name = '';
    public string $slug = '';
    public string $country = '';
    public string $birthdate = '';
    public string $description = '';
    public string $cover_image_url = '';
    public string $uuid = '';
    public int $user_id = 0;

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
    <div>
        <flux:main class="mb-1 space-y-1">
            <flux:heading size="xl" level="1">
                <a href="{{ route('subjects.index') }}"><flux:button size="xs" variant="ghost" icon="arrow-uturn-left"></flux:button></a>
                {{ $this->title }}
            </flux:heading>
            <flux:text class="text-base">{{ $this->subtitle }}</flux:text>
    
            <flux:breadcrumbs>
                <flux:breadcrumbs.item href="{{ route('dashboard') }}">Dashboard</flux:breadcrumbs.item>
                <flux:breadcrumbs.item href="{{ route('subjects.index') }}">Sujetos</flux:breadcrumbs.item>
                <flux:breadcrumbs.item>{{ $this->title }}</flux:breadcrumbs.item>
            </flux:breadcrumbs>
    
            <flux:separator variant="subtle" />
        </flux:main>
    </div>

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