<?php

use App\Models\Page\Subject;
use Livewire\Component;

new class extends Component
{
    //////////////////////////////////////////////////////////////////// PROPIEDADES PRINCIPALES
    //propiedades de titulos
    public string $titlePage = 'Ver juego';
    public string $subtitlePage = 'Ver juego de lista';

    public $game;

    //////////////////////////////////////////////////////////////////// PRE CARGAR DATOS
    // precargar datos al iniciar pagina
    public function mount($gameUuid){
        $this->game = \App\Models\Page\Game::where('user_id', \Illuminate\Support\Facades\Auth::id())
            ->with(['subjects', 'categories', 'collections', 'platforms', 'playeds', 'tags'])
            ->where('uuid', $gameUuid)->first();
    }
};
?>

<div>
     {{-- titulo, descripcion y breadcrumbs --}}
    <x-page.partials.title-page 
        :title="$this->titlePage"
        :create-route="'games.index'"
        icon="arrow-uturn-left"
        :breadcrumbs="[
            ['label' => 'Dashboard', 'route' => 'dashboard'],
            ['label' => 'Juegos', 'route' => 'games.index'],
            ['label' => $this->titlePage]
        ]"
    />

     {{-- toast de mensaje --}}
     <x-libraries.flux.toast-success />

    {{-- datos del juego --}}
    <div class="w-full">
        <x-libraries.img-tumb-lightbox 
            :uri="$game->cover_image_url ?? ''" 
            album="Portadas"
            class_w_h="h-40 sm:h-96 mx-auto"
            class="mx-auto"
        />
        
        <p class="text-xl sm:text-lg font-bold text-gray-900 dark:text-gray-200">{{ $game->title }}</p>
        <p class="text-sm sm:text-sm text-gray-800 dark:text-gray-300 font-light italic">
            <a href="{{ route('games.edit', ['gameUuid' => $game->uuid]) }}"><flux:button size="xs" variant="ghost" icon="pencil-square"></flux:button></a>
            {{ $game->original_title }}
        </p>

        <flux:separator text="Datos" />

        <p class="text-base text-gray-800 dark:text-gray-300 italic">
            | ( {{ $game->release_date }} )
        </p>

        <p class="mt-1">
            @foreach ($game->subjects as $item)
                - 
                <a 
                    class="hover:underline  " 
                    href="{{ route('games_library.index', ['a' => $item->uuid]) }}"
                >{{ $item->name }}</a>
            @endforeach
        </p>

        <p class="mt-3 text-sm text-gray-800 dark:text-gray-300 whitespace-pre-wrap font-light" style="white-space: pre-line;">{{ $game->synopsis }}</p>

        <flux:separator text="Asociaciones" />

        @if (!$game->categories->isEmpty())
            <p class="mt-1 text-sm sm:text-base text-gray-800 dark:text-gray-300 font-bold">
                @foreach ($game->categories as $item)
                    <flux:badge size="sm" variant="pill" as="button" variant="solid" color="purple">
                        <a
                            href="{{ route('games_library.index', ['cat' => $item->uuid]) }}"
                        >{{ $item->name }}</a>
                    </flux:badge>
                @endforeach
            </p>
        @endif

        @if (!$game->tags->isEmpty())
            <p class="mt-1 text-xs sm:text-sm italic text-gray-800 dark:text-gray-300 font-bold">
                @foreach ($game->tags as $item)
                    <a href="#">
                        #{{ $item->name }}
                    </a>
                @endforeach
            </p>
        @endif

        @if (!$game->collections->isEmpty())
            <p class="mt-2 text-sm sm:text-base text-gray-800 dark:text-gray-300 font-bold">
                @foreach ($game->collections as $item)
                    <flux:badge size="sm" variant="pill" as="button" variant="solid" color="purple">
                        <a
                            href="{{ route('games_library.index', ['c' => $item->uuid]) }}"
                        >{{ $item->name }}</a>
                    </flux:badge>
                @endforeach
            </p>
        @endif

        <flux:separator text="Opinion y lecturas" />

        @if ($game->rating)
            <p class="mt-2 text-sm text-gray-950 dark:text-gray-300 italic">Valoracion: {{ str_repeat('⭐', $game->rating) }}</p>
        @endif
        @if ($game->is_favorite)
            <p class="mt-2 text-sm text-gray-950 dark:text-gray-300 italic">{{ $game->is_favorite ? 'Favorito ❤️' : ''  }}</p>
        @endif
        @if ($game->is_abandonated)
            <p class="mt-2 text-sm text-gray-950 dark:text-gray-300 italic">{{ $game->is_abandonated ? 'Abandonado 🚫' : '' }}</p>
        @endif
        
        @if ($game->playeds)
            @foreach ($game->playeds as $played)
            <div class="mt-2 flex items-start justify-between">
                <div class="px-3 border-l-4 border-purple-800">
                    @if ($played->end_played)
                        <p class="text-xs sm:text-sm text-gray-800 dark:text-gray-300 ">{{ \Carbon\Carbon::parse($played->end_played)->format('Y-m-d') }}
                    @endif
                </div>
            </div>
            @endforeach
        @endif

        <flux:separator text="Anotaciones Personales" />

        @if ($game->notes)
            <div class="text-sm text-gray-800 dark:text-gray-300 break-words">
                <p class="text-lg sm:text-xl font-bold text-gray-900 dark:text-gray-300">Reseña ✍️</p>
                <p style="white-space: pre-line;">{!! $game->notes !!}</p>
            </div>
        @endif
        
        @if ($game->summary)
            <div class="text-sm text-gray-800 dark:text-gray-300 break-words">
                <p class="text-lg sm:text-xl font-bold text-gray-900 dark:text-gray-300">Resumen 🗒️</p>
                <p style="white-space: pre-line;">{!! $game->summary !!}</p>
            </div>
        @endif

    </div>
</div>