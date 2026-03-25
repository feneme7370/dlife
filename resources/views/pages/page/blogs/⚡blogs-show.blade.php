<?php

use App\Models\Page\Subject;
use Livewire\Component;

new class extends Component
{
    //////////////////////////////////////////////////////////////////// PROPIEDADES PRINCIPALES
    //propiedades de titulos
    public string $titlePage = 'Ver blog';
    public string $subtitlePage = 'Ver blog de lista';
    public $blog;

    //////////////////////////////////////////////////////////////////// PRE CARGAR DATOS
    // precargar datos al iniciar pagina
    public function mount($blogUuid){
        $this->blog = \App\Models\Page\Blog::where('user_id', \Illuminate\Support\Facades\Auth::id())
            ->with(['tags'])
            ->where('uuid', $blogUuid)->first();
    }

    //////////////////////////////////////////////////////////////////// STORE PARA CREAR O EDITAR
    // cosultas
    public function types(){
        return \App\Models\Page\Blog::types();
    }
};
?>

<div>
    {{-- titulo, descripcion y breadcrumbs --}}
    <x-page.partials.title-page 
        :title="$this->titlePage"
        :create-route="'blogs.index'"
        icon="arrow-uturn-left"
        :breadcrumbs="[
            ['label' => 'Dashboard', 'route' => 'dashboard'],
            ['label' => 'Blogs', 'route' => 'blogs.index'],
            ['label' => $this->titlePage]
        ]"
    />

    {{-- datos del blog --}}
    <div class="w-full ">
        <x-libraries.img-tumb-lightbox 
            :uri="$blog->cover_image_url" 
            album="Portadas"
            class_w_h="h-40 sm:h-96"
            class="mx-auto"
        />

        <p class="text-xl sm:text-lg font-bold text-gray-900 dark:text-gray-200">{{ $blog->title }}</p>
        <p class="text-xs sm:text-sm text-gray-800 dark:text-gray-300 font-light italic">
            <a href="{{ route('blogs.edit', ['blogUuid' => $blog->uuid]) }}"><flux:button size="xs" variant="ghost" icon="pencil-square"></flux:button></a>
        </p>

        <flux:separator text="Datos" />

        <p class="mt-3 text-xs sm:text-sm text-gray-800 dark:text-gray-300 whitespace-pre-wrap font-light" style="white-space: pre-line;">{{ $blog->excerpt }}</p>

        <flux:separator text="Asociaciones" />

        <flux:badge size="sm" variant="pill" as="button" variant="solid" color="purple">
            <a>{{ $this->types()[$blog->type] ?? '' }}</a>
        </flux:badge>

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
                <p class="text-lg sm:text-xl font-bold text-gray-900 dark:text-gray-300">Contenido 🗒️</p>
                <p style="white-space: pre-line;">{!! $blog->content !!}</p>
            </div>
        @endif

    </div>
</div>