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
    <div>
        <div class="mb-1 space-y-1">
            <flux:heading size="xl" level="1">
                <a href="{{ route('series.index') }}"><flux:button size="xs" variant="ghost" icon="arrow-uturn-left"></flux:button></a>
                {{ $this->titlePage }}
            </flux:heading>
            <flux:text class="text-base">{{ $this->subtitlePage }}</flux:text>
    
            <flux:breadcrumbs>
                <flux:breadcrumbs.item href="{{ route('dashboard') }}">Dashboard</flux:breadcrumbs.item>
                <flux:breadcrumbs.item href="{{ route('series.index') }}">Series</flux:breadcrumbs.item>
                <flux:breadcrumbs.item>{{ $this->titlePage }}</flux:breadcrumbs.item>
            </flux:breadcrumbs>
    
            <flux:separator variant="subtle" />
        </div>
    </div>

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
            @foreach ($serie->subjects as $item)
                - 
                <a 
                    class="hover:underline  " 
                    href="{{ route('series_library.index', ['a' => $item->uuid]) }}"
                >{{ $item->name }}</a>
                @endforeach
            ( {{ $serie->start_date }} | {{ $serie->end_date }} )
            | {{$serie->seasons ?? 0}} Temporadas.
            | {{$serie->episodes ?? 0}} Episodios.
        </p>

        <p class="mt-3 text-xs sm:text-sm text-gray-800 dark:text-gray-300 whitespace-pre-wrap font-light" style="white-space: pre-line;">{{ $serie->synopsis }}</p>

        <flux:separator text="Asociaciones" />

        @if (!$serie->genres->isEmpty())
            <p class="mt-1 text-sm sm:text-base text-gray-800 dark:text-gray-300 font-bold">
                Genero:
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
            <p class="mt-1 text-sm sm:text-base text-gray-800 dark:text-gray-300 font-bold">
                Etiquetas:
                @foreach ($serie->tags as $item)
                    <flux:badge class="m-0.5" size="sm" variant="pill" as="button" variant="solid" color="violet">
                        <a
                            href="#"
                        >#{{ $item->name }}</a>
                    </flux:badge>
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