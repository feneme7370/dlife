<?php

use App\Models\Page\Subject;
use Livewire\Component;

new class extends Component
{
    //////////////////////////////////////////////////////////////////// PROPIEDADES PRINCIPALES
    //propiedades de titulos
    public string $titlePage = 'Ver serie';
    public string $subtitlePage = 'Ver serie de lista';

    public $serie;

    //////////////////////////////////////////////////////////////////// PRE CARGAR DATOS
    // precargar datos al iniciar pagina
    public function mount($serieUuid){
        $this->serie = \App\Models\Page\Serie::where('user_id', \Illuminate\Support\Facades\Auth::id())
            ->with(['subjects', 'genres', 'collections', 'views', 'tags'])
            ->where('uuid', $serieUuid)->first();
    }
};
?>

<div>
     {{-- titulo, descripcion y breadcrumbs --}}
    <x-page.partials.title-page 
        :title="$this->titlePage"
        :create-route="'series.index'"
        icon="arrow-uturn-left"
        :breadcrumbs="[
            ['label' => 'Dashboard', 'route' => 'dashboard'],
            ['label' => 'Series', 'route' => 'series.index'],
            ['label' => $this->titlePage]
        ]"
    />

     {{-- toast de mensaje --}}
     <x-libraries.flux.toast-success />

    {{-- datos del serie --}}
    <div class="w-full ">
        <x-libraries.img-tumb-lightbox 
            :uri="$serie->cover_image_url ?? ''" 
            album="Portadas"
            class_w_h="h-40 sm:h-96"
            class="mx-auto"
        />
        
        <p class="text-xl sm:text-lg font-bold text-gray-900 dark:text-gray-200">{{ $serie->title }}</p>
        <p class="text-sm sm:text-sm text-gray-800 dark:text-gray-300 font-light italic">
            <a href="{{ route('series.edit', ['serieUuid' => $serie->uuid]) }}"><flux:button size="xs" variant="ghost" icon="pencil-square"></flux:button></a>
            {{ $serie->original_title }}
        </p>

        <flux:separator text="Datos" />

        <p class="text-base text-gray-800 dark:text-gray-300 italic">
            | {{$serie->seasons ?? 0}} Temporadas.
            | {{$serie->episodes ?? 0}} Episodios.
            | ( {{ $serie->start_date }} / {{ $serie->end_date }} )
        </p>

        <p class="mt-1">
            @foreach ($serie->subjects as $item)
                - 
                <a 
                    class="hover:underline  " 
                    href="{{ route('series_library.index', ['a' => $item->uuid]) }}"
                >{{ $item->name }}</a>
            @endforeach
        </p>

        <p class="mt-1 text-sm text-gray-800 dark:text-gray-300 whitespace-pre-wrap font-light" style="white-space: pre-line;">{{ $serie->synopsis }}</p>

        <flux:separator text="Asociaciones" />

        @if (!$serie->genres->isEmpty())
            <p class="mt-1 text-sm sm:text-base text-gray-800 dark:text-gray-300 font-bold">
                @foreach ($serie->genres as $item)
                    <flux:badge size="sm" variant="pill" as="button" variant="solid" color="purple">
                        <a
                            href="{{ route('series_library.index', ['g' => $item->uuid]) }}"
                        >{{ $item->name }}</a>
                    </flux:badge>
                @endforeach
            </p>
        @endif

        @if (!$serie->tags->isEmpty())
            <p class="mt-1 text-xs sm:text-sm italic text-gray-800 dark:text-gray-300 font-bold">
                @foreach ($serie->tags as $item)
                    <a href="#">#{{ $item->name }}</a>
                @endforeach
            </p>
        @endif

        @if (!$serie->collections->isEmpty())
            <p class="mt-2 text-sm sm:text-base text-gray-800 dark:text-gray-300 font-bold">
                @foreach ($serie->collections as $item)
                    <flux:badge size="sm" variant="pill" as="button" variant="solid" color="purple">
                        <a
                            href="{{ route('series_library.index', ['c' => $item->uuid]) }}"
                        >{{ $item->name }}</a>
                    </flux:badge>
                @endforeach
                <span class="text-xs italic ml-3">Vol. N°{{$serie->number_collection}}</span>
            </p>
        @endif

        <flux:separator text="Opinion y lecturas" />

        @if ($serie->rating)
            <p class="mt-2 text-sm text-gray-950 dark:text-gray-300 italic">Valoracion: {{ str_repeat('⭐', $serie->rating) }}</p>
        @endif
        @if ($serie->is_favorite)
            <p class="mt-2 text-sm text-gray-950 dark:text-gray-300 italic">{{ $serie->is_favorite ? 'Favorito ❤️' : ''  }}</p>
        @endif
        @if ($serie->is_abandonated)
            <p class="mt-2 text-sm text-gray-950 dark:text-gray-300 italic">{{ $serie->is_abandonated ? 'Abandonado 🚫' : '' }}</p>
        @endif
        
        @if ($serie->views)
            @foreach ($serie->views as $view)
            <div class="mt-2 flex items-start justify-between">
                <div class="px-3 border-l-4 border-purple-800">
                    @if ($view->end_view)
                        <p class="text-xs sm:text-sm text-gray-800 dark:text-gray-300 ">{{ \Carbon\Carbon::parse($view->start_view)->format('Y-m-d') }} - {{ \Carbon\Carbon::parse($view->end_view)->format('Y-m-d') }} en {{ \Carbon\Carbon::parse($view->start_view)->diffInDays($view->end_view) }} dias</p>
                    @else
                        <p class="text-xs sm:text-sm text-gray-800 dark:text-gray-300 ">{{ \Carbon\Carbon::parse($view->start_view)->format('Y-m-d') }} Viendo...</p>
                    @endif
                </div>
            </div>
            @endforeach
        @endif

        <flux:separator text="Anotaciones Personales" />

        @if ($serie->notes)
            <div class="text-sm text-gray-800 dark:text-gray-300 break-words">
                <p class="text-lg sm:text-xl font-bold text-gray-900 dark:text-gray-300">Reseña ✍️</p>
                <p style="white-space: pre-line;">{!! $serie->notes !!}</p>
            </div>
        @endif

        @if ($serie->summary)
            <div class="text-sm text-gray-800 dark:text-gray-300 break-words">
                <p class="text-lg sm:text-xl font-bold text-gray-900 dark:text-gray-300">Resumen 🗒️</p>
                <p style="white-space: pre-line;">{!! $serie->summary !!}</p>
            </div>
        @endif

    </div>
</div>