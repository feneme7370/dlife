<?php

use App\Models\Page\Subject;
use Livewire\Component;

new class extends Component
{
    //////////////////////////////////////////////////////////////////// PROPIEDADES PRINCIPALES
    //propiedades de titulos
    public string $title = 'Ver libro';
    public string $subtitle = 'Ver libro de lista';

    public $book;

    //////////////////////////////////////////////////////////////////// PRE CARGAR DATOS
    // precargar datos al iniciar pagina
    public function mount($bookUuid){
        $this->book = \App\Models\Page\Book::where('user_id', \Illuminate\Support\Facades\Auth::id())
            ->with(['subjects', 'genres', 'collections', 'reads', 'tags'])
            ->where('uuid', $bookUuid)->first();
    }
};
?>

<div>
    {{-- titulo, descripcion y breadcrumbs --}}
    <div>
        <div class="mb-1 space-y-1">
            <flux:heading size="xl" level="1">
                <a href="{{ route('books.index') }}"><flux:button size="xs" variant="ghost" icon="arrow-uturn-left"></flux:button></a>
                {{ $this->title }}
            </flux:heading>
            <flux:text class="text-base">{{ $this->subtitle }}</flux:text>
    
            <flux:breadcrumbs>
                <flux:breadcrumbs.item href="{{ route('dashboard') }}">Dashboard</flux:breadcrumbs.item>
                <flux:breadcrumbs.item href="{{ route('books.index') }}">Libros</flux:breadcrumbs.item>
                <flux:breadcrumbs.item>{{ $this->title }}</flux:breadcrumbs.item>
            </flux:breadcrumbs>
    
            <flux:separator variant="subtle" />
        </div>
    </div>

    {{-- datos del libro --}}
    <div class="w-full ">
        <x-libraries.img-tumb-lightbox 
            :uri="$book->cover_image_url" 
            album="Portadas"
            class_w_h="h-40 sm:h-96"
            class="mx-auto"
        />
        {{-- <img src="{{ $book->cover_image_url }}" class="w-full sm:w-auto sm:h-96 mx-auto mb-1" alt="portada"> --}}
        <p class="text-xl sm:text-lg font-bold text-gray-900 dark:text-gray-200">{{ $book->title }}</p>
        <p class="text-xs sm:text-sm text-gray-800 dark:text-gray-300 font-light italic">
            <a href="{{ route('books.edit', ['bookUuid' => $book->uuid]) }}"><flux:button size="xs" variant="ghost" icon="pencil-square"></flux:button></a>
            {{ $book->original_title }}
        </p>

        <flux:separator text="Datos" />

        <p class="text-base text-gray-800 dark:text-gray-300 italic">
            @foreach ($book->subjects as $item)
                - 
                <a 
                    class="hover:underline  " 
                    href="{{ route('books_library.index', ['a' => $item->uuid]) }}"
                >{{ $item->name }}</a>
                @endforeach
            ( {{ $book->release_date }} )
            | {{$book->pages ?? 1}} Pags.
        </p>

        <p class="mt-3 text-xs sm:text-sm text-gray-800 dark:text-gray-300 whitespace-pre-wrap font-light" style="white-space: pre-line;">{{ $book->synopsis }}</p>

        <flux:separator text="Asociaciones" />

        @if (!$book->genres->isEmpty())
            <p class="mt-1 text-sm sm:text-base text-gray-800 dark:text-gray-300 font-bold">
                Genero:
                @foreach ($book->genres as $item)
                    <flux:badge size="sm" variant="pill" as="button" variant="solid" color="purple">
                        <a
                            href="{{ route('books_library.index', ['g' => $item->uuid]) }}"
                        >{{ $item->name }}</a>
                    </flux:badge>
                @endforeach
            </p>
        @endif

        @if (!$book->tags->isEmpty())
            <p class="mt-1 text-sm sm:text-base text-gray-800 dark:text-gray-300 font-bold">
                Etiquetas:
                @foreach ($book->tags as $item)
                    <flux:badge size="sm" variant="pill" as="button" variant="solid" color="violet">
                        <a
                            href="#"
                        >#{{ $item->name }}</a>
                    </flux:badge>
                @endforeach
            </p>
        @endif

        @if (!$book->collections->isEmpty())
            <p class="mt-2 text-sm sm:text-base text-gray-800 dark:text-gray-300 font-bold">
                @foreach ($book->collections as $item)
                    <flux:badge size="sm" variant="pill" as="button" variant="solid" color="purple">
                        <a
                            href="{{ route('books_library.index', ['c' => $item->uuid]) }}"
                        >{{ $item->name }}</a>
                    </flux:badge>
                @endforeach
                <span class="text-xs italic ml-3">Vol. N°{{$book->number_collection}}</span>
            </p>
        @endif

        <flux:separator text="Opinion y lecturas" />

        @if ($book->rating)
            <p class="mt-2 text-sm text-gray-950 dark:text-gray-300 italic">Valoracion: {{ str_repeat('⭐', $book->rating) }}</p>
        @endif
        @if ($book->is_favorite)
            <p class="mt-2 text-sm text-gray-950 dark:text-gray-300 italic">{{ $book->is_favorite ? 'Favorito ❤️' : ''  }}</p>
        @endif
        @if ($book->is_abandonated)
            <p class="mt-2 text-sm text-gray-950 dark:text-gray-300 italic">{{ $book->is_abandonated ? 'Abandonado 🚫' : '' }}</p>
        @endif
        
        @if ($book->reads)
            @foreach ($book->reads as $read)
            <div class="mt-2 flex items-start justify-between">
                <div class="px-3 border-l-4 border-purple-800">
                    @if ($read->end_read)
                        <p class="text-xs sm:text-sm text-gray-800 dark:text-gray-300 ">{{ \Carbon\Carbon::parse($read->start_read)->format('Y-m-d') }} - {{ \Carbon\Carbon::parse($read->end_read)->format('Y-m-d') }} en {{ \Carbon\Carbon::parse($read->start_read)->diffInDays($read->end_read) }} dias</p>
                    @else
                        <p class="text-xs sm:text-sm text-gray-800 dark:text-gray-300 ">{{ \Carbon\Carbon::parse($read->start_read)->format('Y-m-d') }} - {{ \Carbon\Carbon::parse($read->end_read)->format('Y-m-d') }} Leyendo...</p>
                    @endif
                </div>
            </div>
            @endforeach
        @endif

        <flux:separator text="Anotaciones Personales" />

        @if ($book->summary)
            <div class="text-sm text-gray-800 dark:text-gray-300 break-words">
                <p class="text-lg sm:text-xl font-bold text-gray-900 dark:text-gray-300">Resumen 🗒️</p>
                <p style="white-space: pre-line;">{!! $book->summary !!}</p>
            </div>
        @endif

        @if ($book->notes)
            <div class="text-sm text-gray-800 dark:text-gray-300 break-words">
                <p class="text-lg sm:text-xl font-bold text-gray-900 dark:text-gray-300">Reseña ✍️</p>
                <p style="white-space: pre-line;">{!! $book->notes !!}</p>
            </div>
        @endif

        {{-- <flux:separator text="Anotaciones Personales" />

        @if ($book->summary_clear)
            <div class="text-sm text-gray-800 dark:text-gray-300">
                <p class="text-lg sm:text-xl font-bold text-gray-900 dark:text-gray-300">Resumen 🗒️</p>
                <p style="white-space: pre-line;">{{ $book->summary_clear }}</p>
            </div>
        @endif

        @if ($book->notes_clear)
            <div class="text-sm text-gray-800 dark:text-gray-300">
                <p class="text-lg sm:text-xl font-bold text-gray-900 dark:text-gray-300">Reseña ✍️</p>
                <p style="white-space: pre-line;">{{ $book->notes_clear }}</p>
            </div>
        @endif --}}

    </div>
</div>