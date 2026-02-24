<?php

use App\Models\Page\BookGenre;
use Livewire\Component;

new class extends Component
{
    //propiedades de titulos
    public string $title = 'Ver genero';
    public string $subtitle = 'Ver generos de libros';

    // propiedades del item
    public string $name = '';
    public string $slug = '';
    public string $name_general = '';
    public string $slug_general = '';
    public string $description = '';
    public string $cover_image_url = '';
    public string $uuid = '';
    public int $user_id = 0;

    // precargar datos al iniciar pagina
    public function mount($bookGenreUuid){
        $item = BookGenre::where('uuid', $bookGenreUuid)->first();
        $this->name = $item->name ?? '';
        $this->slug = $item->slug ?? '';
        $this->name_general = $item->name_general ?? '';
        $this->slug_general = $item->slug_general ?? '';
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
                <a href="{{ route('book-genres.index') }}"><flux:button size="xs" variant="ghost" icon="arrow-uturn-left"></flux:button></a>
                {{ $this->title }}
            </flux:heading>
            <flux:text class="text-base">{{ $this->subtitle }}</flux:text>
    
            <flux:breadcrumbs>
                <flux:breadcrumbs.item href="{{ route('dashboard') }}">Dashboard</flux:breadcrumbs.item>
                <flux:breadcrumbs.item href="{{ route('book-genres.index') }}">Generos</flux:breadcrumbs.item>
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
            <p class="text-sm italic">{{ $this->name_general }}</p>
            <p class="mt-3">{{ $this->description }}</p>
        </div>
    </div>
</div>