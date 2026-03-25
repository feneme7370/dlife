<?php

use App\Models\Page\Subject;
use Livewire\Component;

new class extends Component
{
    //////////////////////////////////////////////////////////////////// PROPIEDADES PRINCIPALES
    //propiedades de titulos
    public string $titlePage = 'Ver pelicula';
    public string $subtitlePage = 'Ver pelicula de lista';

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
    <x-page.partials.title-page 
        :title="$this->titlePage"
        :create-route="'movies.index'"
        icon="arrow-uturn-left"
        :breadcrumbs="[
            ['label' => 'Dashboard', 'route' => 'dashboard'],
            ['label' => 'Peliculas', 'route' => 'movies.index'],
            ['label' => $this->titlePage]
        ]"
    />

     {{-- toast de mensaje --}}
     <x-libraries.flux.toast-success />

    {{-- datos del pelicula --}}
    <div class="w-full">
        <x-libraries.img-tumb-lightbox 
            :uri="$movie->cover_image_url ?? ''" 
            album="Portadas"
            class_w_h="h-40 sm:h-96 mx-auto"
            class="mx-auto"
        />
        
        <p class="text-xl sm:text-lg font-bold text-gray-900 dark:text-gray-200">{{ $movie->title }}</p>
        <p class="text-sm sm:text-sm text-gray-800 dark:text-gray-300 font-light italic">
            <a href="{{ route('movies.edit', ['movieUuid' => $movie->uuid]) }}"><flux:button size="xs" variant="ghost" icon="pencil-square"></flux:button></a>
            {{ $movie->original_title }}
        </p>

        <flux:separator text="Datos" />

        <p class="text-base text-gray-800 dark:text-gray-300 italic">
            | {{$movie->runtime ?? 1}} Mins.
            | ( {{ $movie->release_date }} )
        </p>

        <p class="mt-1">
            @foreach ($movie->subjects as $item)
                - 
                <a 
                    class="hover:underline  " 
                    href="{{ route('movies_library.index', ['a' => $item->uuid]) }}"
                >{{ $item->name }}</a>
            @endforeach
        </p>

        <p class="mt-3 text-sm text-gray-800 dark:text-gray-300 whitespace-pre-wrap font-light" style="white-space: pre-line;">{{ $movie->synopsis }}</p>

        <flux:separator text="Asociaciones" />

        @if (!$movie->genres->isEmpty())
            <p class="mt-1 text-sm sm:text-base text-gray-800 dark:text-gray-300 font-bold">
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
            <p class="mt-1 text-xs sm:text-sm italic text-gray-800 dark:text-gray-300 font-bold">
                @foreach ($movie->tags as $item)
                    <a href="#">
                        #{{ $item->name }}
                    </a>
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

        @if ($movie->notes)
            <div class="text-sm text-gray-800 dark:text-gray-300 break-words">
                <p class="text-lg sm:text-xl font-bold text-gray-900 dark:text-gray-300">Reseña ✍️</p>
                <p style="white-space: pre-line;">{!! $movie->notes !!}</p>
            </div>
        @endif
        
        @if ($movie->summary)
            <div class="text-sm text-gray-800 dark:text-gray-300 break-words">
                <p class="text-lg sm:text-xl font-bold text-gray-900 dark:text-gray-300">Resumen 🗒️</p>
                <p style="white-space: pre-line;">{!! $movie->summary !!}</p>
            </div>
        @endif

    </div>
</div>