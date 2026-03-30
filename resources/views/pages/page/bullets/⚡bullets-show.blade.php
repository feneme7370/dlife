<?php

use App\Models\Page\Subject;
use Livewire\Component;

new class extends Component
{
    //////////////////////////////////////////////////////////////////// PROPIEDADES PRINCIPALES
    //propiedades de titulos
    public string $titlePage = 'Ver bullet';
    public string $subtitlePage = 'Ver bullet de lista';
    public $blog;

    //////////////////////////////////////////////////////////////////// PRE CARGAR DATOS
    // precargar datos al iniciar pagina
    public function mount($bulletUuid){
        $this->blog = \App\Models\Page\Blog::where('user_id', \Illuminate\Support\Facades\Auth::id())
            ->with(['tags'])
            ->where('uuid', $bulletUuid)->first();
    }

    //////////////////////////////////////////////////////////////////// STORE PARA CREAR O EDITAR
    // cosultas
    public function types(){
        return \App\Models\Page\Blog::bullet_types();
    }
};
?>

<div>
    {{-- titulo, descripcion y breadcrumbs --}}
    <x-page.partials.title-page 
        :title="$this->titlePage"
        :create-route="'bullets.index'"
        icon="arrow-uturn-left"
        :breadcrumbs="[
            ['label' => 'Dashboard', 'route' => 'dashboard'],
            ['label' => 'BuJo', 'route' => 'bullets.index'],
            ['label' => $this->titlePage]
        ]"
    />

    {{-- datos del blog --}}
    <div class="w-full ">

        <div class="flex items-center gap-5 mb-5">
            <p class="text-xl sm:text-lg font-bold text-gray-900 dark:text-gray-200">{{ $blog->year }}</p>
            <flux:badge size="sm" variant="pill" as="button" variant="solid" color="purple">
                <a>{{ $this->types()[$blog->type] ?? '' }}</a>
            </flux:badge>
        </div>
        <p class="text-lg sm:text-lg font-bold text-gray-900 dark:text-gray-200">
            <a href="{{ route('bullets.edit', ['bulletUuid' => $blog->uuid]) }}"><flux:button size="xs" variant="ghost" icon="pencil-square"></flux:button></a>
            {{ $blog->title }}
        </p>

        <p class="mt-3 text-xs sm:text-sm text-gray-800 dark:text-gray-300 whitespace-pre-wrap font-light" style="white-space: pre-line;">{{ $blog->excerpt }}</p>

        @if (!$blog->tags->isEmpty())
            <p class="mt-1 text-sm sm:text-base text-gray-800 dark:text-gray-300 font-bold">
                Etiquetas:
                @foreach ($blog->tags as $item)
                    <flux:badge size="sm" variant="pill" as="button" variant="solid" color="violet">
                        <a
                            href="#"
                        >#{{ $item->name }}</a>
                    </flux:badge>
                @endforeach
            </p>
        @endif

        <flux:separator text="Anotaciones Personales" />

        @if ($blog->content)
            <div class="text-sm text-gray-800 dark:text-gray-300 break-words">
                <p class="text-lg sm:text-xl font-bold text-gray-900 dark:text-gray-300 mb-2">Contenido 🗒️</p>
                <p style="white-space: pre-line;">{!! $blog->content !!}</p>
            </div>
        @endif

    </div>
</div>