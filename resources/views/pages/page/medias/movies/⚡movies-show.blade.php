<?php

use App\Models\Page\Subject;
use Livewire\Component;

new class extends Component
{
    //////////////////////////////////////////////////////////////////// PROPIEDADES PRINCIPALES
    //propiedades de titulos
    public string $title = 'Ver pelicula';
    public string $subtitle = 'Ver pelicula de lista';

    public $movie;

    //////////////////////////////////////////////////////////////////// PRE CARGAR DATOS
    // precargar datos al iniciar pagina
    public function mount($movieUuid){
        $this->movie = \App\Models\Page\Movie::where('user_id', \Illuminate\Support\Facades\Auth::id())
            ->with(['subjects', 'genres', 'collections', 'views', 'tags'])
            ->where('uuid', $movieUuid)->first();
    }
};
?>

<div>
    {{-- titulo, descripcion y breadcrumbs --}}
    <div>
        <div class="mb-1 space-y-1">
            <flux:heading size="xl" level="1">
                <a href="{{ route('movies.index') }}"><flux:button size="xs" variant="ghost" icon="arrow-uturn-left"></flux:button></a>
                {{ $this->title }}
            </flux:heading>
            <flux:text class="text-base">{{ $this->subtitle }}</flux:text>
    
            <flux:breadcrumbs>
                <flux:breadcrumbs.item href="{{ route('dashboard') }}">Dashboard</flux:breadcrumbs.item>
                <flux:breadcrumbs.item href="{{ route('movies.index') }}">Peliculas</flux:breadcrumbs.item>
                <flux:breadcrumbs.item>{{ $this->title }}</flux:breadcrumbs.item>
            </flux:breadcrumbs>
    
            <flux:separator variant="subtle" />
        </div>
    </div>

    {{-- datos del pelicula --}}
    <div class="w-full ">
        <x-libraries.img-tumb-lightbox 
            :uri="$movie->cover_image_url ?? ''" 
            album="Portadas"
            class_w_h="h-40 sm:h-96"
            class="mx-auto"
        />
        {{-- <img src="{{ $movie->cover_image_url }}" class="w-full sm:w-auto sm:h-96 mx-auto mb-1" alt="portada"> --}}
        <p class="text-xl sm:text-lg font-bold text-gray-900 dark:text-gray-200">{{ $movie->title }}</p>
        <p class="text-xs sm:text-sm text-gray-800 dark:text-gray-300 font-light italic">
            <a href="{{ route('movies.edit', ['movieUuid' => $movie->uuid]) }}"><flux:button size="xs" variant="ghost" icon="pencil-square"></flux:button></a>
            {{ $movie->original_title }}
        </p>

        <flux:separator text="Datos" />

        <p class="text-base text-gray-800 dark:text-gray-300 italic">
            @foreach ($movie->subjects as $item)
                - 
                <a 
                    class="hover:underline  " 
                    href="{{ route('movies_library.index', ['a' => $item->uuid]) }}"
                >{{ $item->name }}</a>
                @endforeach
            ( {{ $movie->release_date }} )
            | {{$movie->runtime ?? 1}} Mins.
        </p>

        <p class="mt-3 text-xs sm:text-sm text-gray-800 dark:text-gray-300 whitespace-pre-wrap font-light" style="white-space: pre-line;">{{ $movie->synopsis }}</p>

        <flux:separator text="Asociaciones" />

        @if (!$movie->genres->isEmpty())
            <p class="mt-1 text-sm sm:text-base text-gray-800 dark:text-gray-300 font-bold">
                Genero:
                @foreach ($movie->genres as $item)
                    <flux:badge size="sm" variant="pill" as="button" variant="solid" color="purple">
                        <a
                            href="{{ route('movies_library.index', ['g' => $item->uuid]) }}"
                        >{{ $item->name }}</a>
                    </flux:badge>
                @endforeach
            </p>
        @endif

        @if (!$movie->tags->isEmpty())
            <p class="mt-1 text-sm sm:text-base text-gray-800 dark:text-gray-300 font-bold">
                Etiquetas:
                @foreach ($movie->tags as $item)
                    <flux:badge size="sm" variant="pill" as="button" variant="solid" color="violet">
                        <a
                            href="#"
                        >#{{ $item->name }}</a>
                    </flux:badge>
                @endforeach
            </p>
        @endif

        @if (!$movie->collections->isEmpty())
            <p class="mt-2 text-sm sm:text-base text-gray-800 dark:text-gray-300 font-bold">
                @foreach ($movie->collections as $item)
                    <flux:badge size="sm" variant="pill" as="button" variant="solid" color="purple">
                        <a
                            href="{{ route('movies_library.index', ['c' => $item->uuid]) }}"
                        >{{ $item->name }}</a>
                    </flux:badge>
                @endforeach
                <span class="text-xs italic ml-3">Vol. N°{{$movie->number_collection}}</span>
            </p>
        @endif

        <flux:separator text="Opinion y lecturas" />

        @if ($movie->rating)
            <p class="mt-2 text-sm text-gray-950 dark:text-gray-300 italic">Valoracion: {{ str_repeat('⭐', $movie->rating) }}</p>
        @endif
        @if ($movie->is_favorite)
            <p class="mt-2 text-sm text-gray-950 dark:text-gray-300 italic">{{ $movie->is_favorite ? 'Favorito ❤️' : ''  }}</p>
        @endif
        @if ($movie->is_abandonated)
            <p class="mt-2 text-sm text-gray-950 dark:text-gray-300 italic">{{ $movie->is_abandonated ? 'Abandonado 🚫' : '' }}</p>
        @endif
        
        @if ($movie->views)
            @foreach ($movie->views as $view)
            <div class="mt-2 flex items-start justify-between">
                <div class="px-3 border-l-4 border-purple-800">
                    @if ($view->end_view)
                        <p class="text-xs sm:text-sm text-gray-800 dark:text-gray-300 ">{{ \Carbon\Carbon::parse($view->end_view)->format('Y-m-d') }}
                    @endif
                </div>
            </div>
            @endforeach
        @endif

        <flux:separator text="Anotaciones Personales" />

        @if ($movie->summary)
            <div class="text-sm text-gray-800 dark:text-gray-300 break-words">
                <p class="text-lg sm:text-xl font-bold text-gray-900 dark:text-gray-300">Resumen 🗒️</p>
                <p style="white-space: pre-line;">{!! $movie->summary !!}</p>
            </div>
        @endif

        @if ($movie->notes)
            <div class="text-sm text-gray-800 dark:text-gray-300 break-words">
                <p class="text-lg sm:text-xl font-bold text-gray-900 dark:text-gray-300">Reseña ✍️</p>
                <p style="white-space: pre-line;">{!! $movie->notes !!}</p>
            </div>
        @endif

    </div>
</div>