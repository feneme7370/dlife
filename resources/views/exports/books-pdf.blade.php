<!DOCTYPE html>
<html>
<head>

<meta charset="utf-8">
@vite(['resources/css/app.css', 'resources/js/app.js'])
@fluxAppearance

</head>

<body class="bg-white text-zinc-800">

@foreach($books as $book)

    <div class="w-full break-after-page">

        <div class="w-40 h-60 mx-auto mb-4 flex items-center justify-center bg-zinc-100 rounded overflow-hidden">
            <img 
                src="{{ $book->cover_image_url }}" 
                class="w-56 h-80 object-contain"
                alt="portada"
            >
        </div>
        <p class="text-xl sm:text-lg font-bold text-gray-900 dark:text-gray-200">{{ $book->title }}</p>
        <p class="text-sm sm:text-sm text-gray-800 dark:text-gray-300 font-light italic">
            {{ $book->original_title }}
        </p>

        <flux:separator text="Datos" />

        <p class="text-base text-gray-800 dark:text-gray-300 italic">
            @foreach ($book->subjects as $item)
                - 
                <a 
                    class="hover:underline  " 
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
                        >{{ $item->name }}</a>
                    </flux:badge>
                @endforeach
            </p>
        @endif

        @if (!$book->tags->isEmpty())
            <p class="mt-1 text-sm sm:text-base text-gray-800 dark:text-gray-300 font-bold">
                Etiquetas:
                @foreach ($book->tags as $item)
                    <flux:badge class="m-0.5" size="sm" variant="pill" as="button" variant="solid" color="violet">
                        <a
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

        @if ($book->notes)
            <div class="text-sm text-gray-800 dark:text-gray-300 break-words">
                <p class="text-lg sm:text-xl font-bold text-gray-900 dark:text-gray-300">Reseña ✍️</p>
                <p style="white-space: pre-line;">{!! $book->notes !!}</p>
            </div>
        @endif
        
        {{-- @if ($book->summary)
            <div class="text-sm text-gray-800 dark:text-gray-300 break-words">
                <p class="text-lg sm:text-xl font-bold text-gray-900 dark:text-gray-300">Resumen 🗒️</p>
                <p style="white-space: pre-line;">{!! $book->summary !!}</p>
            </div>
        @endif --}}

    </div>

@endforeach

</body>
</html>