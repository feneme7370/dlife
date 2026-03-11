<?php

use App\Models\Page\Subject;
use Livewire\Component;

new class extends Component
{
    //////////////////////////////////////////////////////////////////// PROPIEDADES PRINCIPALES
    //propiedades de titulos
    public string $titlePage = 'Ver receta';
    public string $subtitlePage = 'Ver receta de lista';
    public $recipe;

    //////////////////////////////////////////////////////////////////// PRE CARGAR DATOS
    // precargar datos al iniciar pagina
    public function mount($recipeUuid){
        $this->recipe = \App\Models\Page\Recipe::where('user_id', \Illuminate\Support\Facades\Auth::id())
            ->with(['categories', 'tags'])
            ->where('uuid', $recipeUuid)->first();
    }
};
?>

<div>
    {{-- titulo, descripcion y breadcrumbs --}}
    <div>
        <div class="mb-1 space-y-1">
            <flux:heading size="xl" level="1">
                <a href="{{ route('recipes.index') }}"><flux:button size="xs" variant="ghost" icon="arrow-uturn-left"></flux:button></a>
                {{ $this->titlePage }}
            </flux:heading>
            <flux:text class="text-base">{{ $this->subtitlePage }}</flux:text>
    
            <flux:breadcrumbs>
                <flux:breadcrumbs.item href="{{ route('dashboard') }}">Dashboard</flux:breadcrumbs.item>
                <flux:breadcrumbs.item href="{{ route('recipes.index') }}">Recetas</flux:breadcrumbs.item>
                <flux:breadcrumbs.item>{{ $this->titlePage }}</flux:breadcrumbs.item>
            </flux:breadcrumbs>
    
            <flux:separator variant="subtle" />
        </div>
    </div>

    {{-- datos del libro --}}
    <div class="w-full ">
        <x-libraries.img-tumb-lightbox 
            :uri="$recipe->cover_image_url" 
            album="Portadas"
            class_w_h="h-40 sm:h-96"
            class="mx-auto"
        />

        <p class="text-xl sm:text-lg font-bold text-gray-900 dark:text-gray-200">{{ $recipe->title }}</p>
        <p class="text-xs sm:text-sm text-gray-800 dark:text-gray-300 font-light italic">
            <a href="{{ route('recipes.edit', ['recipeUuid' => $recipe->uuid]) }}"><flux:button size="xs" variant="ghost" icon="pencil-square"></flux:button></a>
        </p>

        <flux:separator text="Datos" />

        <p class="mt-3 text-xs sm:text-sm text-gray-800 dark:text-gray-300 whitespace-pre-wrap font-light" style="white-space: pre-line;">{{ $recipe->description }}</p>

        <flux:separator text="Asociaciones" />

        @if (!$recipe->categories->isEmpty())
            <p class="mt-1 text-sm sm:text-base text-gray-800 dark:text-gray-300 font-bold">
                Genero:
                @foreach ($recipe->categories as $item)
                    <flux:badge size="sm" variant="pill" as="button" variant="solid" color="purple">
                        <a
                            href="{{ route('recipes_library.index', ['cat' => $item->uuid]) }}"
                        >{{ $item->name }}</a>
                    </flux:badge>
                @endforeach
            </p>
        @endif

        @if (!$recipe->tags->isEmpty())
            <p class="mt-1 text-sm sm:text-base text-gray-800 dark:text-gray-300 font-bold">
                Etiquetas:
                @foreach ($recipe->tags as $item)
                    <flux:badge size="sm" variant="pill" as="button" variant="solid" color="violet">
                        <a
                            href="#"
                        >#{{ $item->name }}</a>
                    </flux:badge>
                @endforeach
            </p>
        @endif

        <flux:separator text="Anotaciones Personales" />

        @if ($recipe->ingredients)
            <div class="text-sm text-gray-800 dark:text-gray-300 break-words">
                <p class="text-lg sm:text-xl font-bold text-gray-900 dark:text-gray-300">Ingredientes 🗒️</p>
                <p style="white-space: pre-line;">{!! $recipe->ingredients !!}</p>
            </div>
        @endif

        @if ($recipe->instructions)
            <div class="text-sm text-gray-800 dark:text-gray-300 break-words">
                <p class="text-lg sm:text-xl font-bold text-gray-900 dark:text-gray-300">Instrucciones ✍️</p>
                <p style="white-space: pre-line;">{!! $recipe->instructions !!}</p>
            </div>
        @endif

    </div>
</div>