<?php

use App\Models\Page\Game;
use App\Models\Page\Category;
use App\Models\Page\Collection;
use App\Models\Page\Platform;
use App\Models\Page\Subject;
use Livewire\Component;

new class extends Component
{
    use \App\Traits\HandlesTags;
    use \App\Traits\CleansHtml;
    use \App\Traits\WithCollections;
    use \App\Traits\WithSubjects;

    //////////////////////////////////////////////////////////////////// PROPIEDADES PRINCIPALES
    //propiedades de titulos
    public string $titlePage = '';
    public string $subtitlePage = '';
    public string $buttonSubmit = '';

    public string $title = '';
    public string $slug = '';
    public string $original_title = '';
    public string $synopsis = '';
    public int $release_date = 1;
    public string $summary = '';
    public string $summary_clear = '';
    public string $notes = '';
    public string $notes_clear = '';
    public bool $is_favorite = false;
    public bool $is_abandonated = false;
    public int $type = 1;
    public int $rating = 0;
    public string $cover_image_url = '';
    public string $uuid = '';
    public int $user_id = 0;

    // propiedades para relacion muchos a muchos
    public $selectedGameSubjects = [];
    public $selectedGameCollections = [];
    public $selectedGamesCategories = [];
    public $selectedGameTags = [];
    public $selectedGamePlatforms = [];

    // propiedades para jugadas
    public $start_played = null;
    public $end_played = null;

    // propiedades del item
    public $game;
    public $playeds, $playedId;

    //////////////////////////////////////////////////////////////////// BUSCAR EN API TMDB LOS DATOS
    public $searchGame = '';
    public $results = [];
    public $devs_recommended = [];
    public $categories_recommended = '';
    public $platforms_recommended = '';
    public $selectedGame = null;

    public function searchGames(){
        if(strlen($this->searchGame) < 3){
            $this->results = [];
            return;
        }

        $response = \Illuminate\Support\Facades\Http::get('https://api.rawg.io/api/games', [
            'key' => env('RAWG_API_KEY'),
            'search' => $this->searchGame,
            'language' => 'es-MX',
        ]);

        $this->results = collect($response->json()['results'])
            ->take(10)
            ->toArray();

        // dd($this->results);
        // $this->results = $response->json()['results'] ?? [];
    }
    public function selectGame($id){
        $response = \Illuminate\Support\Facades\Http::get("https://api.rawg.io/api/games/{$id}", [
            'key' => env('RAWG_API_KEY'),
        ]);

        $selectedGame = $response->json();
        // dd($selectedGame);
        // autocompletar campos
        $this->title = $selectedGame['name'];
        $this->original_title = $selectedGame['name_original'];
        $this->synopsis = $selectedGame['description'];
        $this->release_date = substr($selectedGame['released'], 0, 4);
        $this->cover_image_url = $selectedGame['background_image'];
        // $this->devs_recommended = collect($credits->json()['cast'])->take(10)->pluck('name')->toArray();
        $this->devs_recommended = collect($selectedGame['developers'])->take(10)->pluck('name')->toArray();
        $this->categories_recommended = collect($selectedGame['genres'])->take(10)->pluck('name')->toArray();
        $this->platforms_recommended = collect($selectedGame['platforms'])->take(10)->toArray();

        // cerrar modal
        $this->modal('select-game-api')->close();
    }

    //////////////////////////////////////////////////////////////////// VALIDACIONES
    // reglas de validacion
    protected function rules(){
        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', \Illuminate\Validation\Rule::unique('games', 'slug')->ignore($this->game?->id ?? 0)],
            'original_title' => ['nullable', 'string', 'max:255'],
            'synopsis' => ['nullable', 'string'],
            'release_date' => ['required', 'integer', 'min:1'],
            'summary' => ['nullable', 'string'],
            'summary_clear' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
            'notes_clear' => ['nullable', 'string'],
            'is_favorite' => ['nullable', 'bool'],
            'is_abandonated' => ['nullable', 'bool'],
            'type' => ['required', 'integer', 'min:1'],
            'rating' => ['required', 'integer', 'min:0', 'max:5'],
            'cover_image_url' => ['nullable', 'url', 'max:65535'],
            'uuid' => ['required', 'string', 'max:255', \Illuminate\Validation\Rule::unique('games', 'uuid')->ignore($this->game?->id ?? 0)],
            'user_id' => ['required', 'exists:users,id'],
        ];
    }

    // renombrar variables a castellano
    protected $validationAttributes = [
        'title' => 'titulo',
        'slug' => 'slug',
        'original_title' => 'titulo original',
        'synopsis' => 'sinopsis',
        'release_date' => 'publicacion',
        'summary' => 'resumen personal',
        'summary' => 'resumen personal limpio',
        'notes' => 'notas',
        'notes' => 'notas limpias',
        'is_favorite' => 'favorito',
        'is_favorite' => 'abandonado',
        'type' => 'tipo',
        'rating' => 'valoracion',
        'cover_image_url' => 'URL de imagen de portada',
        'uuid' => 'UUID',
        'user_id' => 'usuario',
    ];

    //////////////////////////////////////////////////////////////////// DATOS PRECARGADOS
    // cargar datos del juego
    public function mount($gameUuid = null){
        // traer datos de juego
        $this->game = Game::where('user_id', \Illuminate\Support\Facades\Auth::id())
            ->with(['subjects', 'categories', 'collections', 'tags', 'playeds'])
            ->where('uuid', $gameUuid)->first();

        // titulos y textos dependiendo si se encuentra el item o no
        $this->titlePage = $this->game ? 'Modificar juego' : 'Agregar juego';
        $this->subtitlePage = $this->game ? 'Modificar datos de la juego' : 'Agregar datos de la juego';
        $this->buttonSubmit = $this->game ? 'Modificar' : 'Agregar';

        if($this->game){
            // poner datos a las propiedades
            $this->title = $this->game->title;
            $this->slug = $this->game->slug;
            $this->original_title = $this->game->original_title ?? '';
            $this->synopsis = $this->game->synopsis ?? '';
            $this->release_date = $this->game->release_date ?? 1;
            $this->summary = $this->game->summary ?? '';
            $this->summary_clear = $this->game->summary_clear ?? '';
            $this->notes = $this->game->notes ?? '';
            $this->notes_clear = $this->game->notes_clear ?? '';
            $this->is_favorite = $this->game->is_favorite ?? false;
            $this->is_abandonated = $this->game->is_abandonated ?? false;
            $this->type = $this->game->type ?? 1;
            $this->rating = $this->game->rating ?? 0;
            $this->cover_image_url = $this->game->cover_image_url ?? '';
            $this->user_id = $this->game->user_id;
            $this->uuid = $this->game->uuid;
    
            // poner en arrays las asociaciones de m2m
            $this->selectedGamesCategories = $this->game->categories->pluck('id')->toArray() ?? [];
            $this->selectedGameSubjects = $this->game->subjects->pluck('id')->toArray() ?? [];
            $this->selectedGameCollections = $this->game->collections->pluck('id')->toArray() ?? [];
            $this->selectedGamePlatforms = $this->game->platforms->pluck('id')->toArray() ?? [];
            $this->selectedGameTags = $this->game->tags->pluck('name')->toArray() ?? [];
        }
    }

    //////////////////////////////////////////////////////////////////// DATOS PARA ASOCIAR
    // traer datos de generos para asociar
    public function categories(){
        return Category::where('category_type', 'games')->where('user_id', \Illuminate\Support\Facades\Auth::id())->orderBy('name', 'asc')->get();
    }
    // traer datos de colecciones para asociar
    public function collections(){
        return Collection::where('user_id', \Illuminate\Support\Facades\Auth::id())->orderBy('name', 'asc')->get();
    }
    // traer datos de generos para asociar
    public function subjects(){
        return Subject::where('user_id', \Illuminate\Support\Facades\Auth::id())->orderBy('name', 'asc')->get();
    }
    // traer tipos
    public function platforms(){
        return Platform::where('user_id', \Illuminate\Support\Facades\Auth::id())->orderBy('name', 'asc')->get();
    }

    //////////////////////////////////////////////////////////////////// MODAL PARA CARGAR READS
    // abrir modal de nota
    public function modalPlayed(){
        $this->start_played = null;
        $this->end_played = null;
        $this->modal('add-played')->show();
    }

    // agregar jugada
    public function addPlayed(){

        $this->validate([
            'start_played' => ['required', 'date'],
            'end_played' => ['nullable', 'date'],
        ]);

        \App\Models\Page\GamePlayed::create([
            'user_id' => \Illuminate\Support\Facades\Auth::id(),
            'game_id' => $this->game->id,
            'start_played' => $this->start_played,
            'end_played' => $this->end_played,
        ]);

        $this->playeds = \App\Models\Page\GamePlayed::where('game_id', $this->game->id)->get();
        $this->start_played = '';
        $this->end_played = '';

        $this->modal('add-played')->close();

        session()->flash('success', 'Juegado agregado');
    }

    // modal para borrar nota
    public function deletePlayed($id){
        $this->playedId = $id;
        $this->modal('delete-played')->show();
    }
    // borrar nota
    public function destroyPlayed(){
        \App\Models\Page\GamePlayed::find($this->playedId)->delete();
        $this->modal('delete-played')->close();
        $this->playeds = \App\Models\Page\GamePlayed::where('game_id', $this->game->id)->get();
        $this->playedId = '';
        session()->flash('success', 'Juegado eliminado');
    }

    //////////////////////////////////////////////////////////////////// EDITAR DATOS
    // crear item en la BD
    public function updateItem(){
        // datos automaticos
        $this->title = \Illuminate\Support\Str::title(trim($this->title));
        $this->slug = \Illuminate\Support\Str::slug($this->title . '-' . \Illuminate\Support\Facades\Auth::id());
        $this->summary_clear = $this->cleanNotes($this->summary);
        $this->notes_clear = $this->cleanNotes($this->notes);

        if($this->game){
            // validar
            $validatedData = $this->validate();

            // crear en BD
            $this->game->update($validatedData);
            $this->game->subjects()->sync($this->selectedGameSubjects);
            $this->game->collections()->sync($this->selectedGameCollections);
            $this->game->categories()->sync($this->selectedGamesCategories);
            $this->game->platforms()->sync($this->selectedGamePlatforms);

            // agregar tags
            $tagIds = [];
            foreach ($this->selectedGameTags as $tagName) {
                $tag = \App\Models\Page\Tag::firstOrCreate(
                    ['name' => $tagName],
                    [
                        'slug' => \Illuminate\Support\Str::slug($tagName),
                        'tag_type' => 'games',
                        'uuid' => \Illuminate\Support\Str::random(24),
                        'user_id' => \Illuminate\Support\Facades\Auth::id(),
                    ]
                );

                $tagIds[] = $tag->id;
            }
            $this->game->tags()->sync($tagIds);
        }else{
            // datos automaticos
            $this->user_id = \Illuminate\Support\Facades\Auth::id();
            $this->uuid = \Illuminate\Support\Str::random(24);

            // validar
            $validatedData = $this->validate();

            // crear en BD
            $game = Game::create($validatedData);
            $game->subjects()->sync($this->selectedGameSubjects);
            $game->collections()->sync($this->selectedGameCollections);
            $game->categories()->sync($this->selectedGamesCategories);
            $game->platforms()->sync($this->selectedGamePlatforms);

            // agregar played
            if($this->start_played || $this->end_played){
                \App\Models\Page\GamePlayed::create([
                    'user_id' => \Illuminate\Support\Facades\Auth::id(),
                    'game_id' => $game->id,
                    'start_played' => $this->start_played,
                    'end_played' => $this->end_played,
                ]);
            };

            // agregar tags
            $tagIds = [];
            foreach ($this->selectedGameTags as $tagName) {
                $tag = \App\Models\Page\Tag::firstOrCreate(
                    ['name' => $tagName],
                    [
                        'slug' => \Illuminate\Support\Str::slug($tagName),
                        'tag_type' => 'games',
                        'uuid' => \Illuminate\Support\Str::random(24),
                        'user_id' => \Illuminate\Support\Facades\Auth::id(),
                    ]
                );

                $tagIds[] = $tag->id;
            }
            $game->tags()->sync($tagIds);
        }

        // mensaje de success
        session()->flash('success', $this->game ? 'Editado correctamente' : 'Creado correctamente');

        // redireccionar
        $this->redirectRoute('games.index', navigate:true);
    }
};
?>

<div>
     {{-- titulo, descripcion y bplayedcrumbs --}}
    <x-page.partials.title-page 
        :title="$this->titlePage"
        :create-route="'games.index'"
        icon="arrow-uturn-left"
        :bplayedcrumbs="[
            ['label' => 'Dashboard', 'route' => 'dashboard'],
            ['label' => 'Juegos', 'route' => 'games.index'],
            ['label' => $this->titlePage]
        ]"
    />

     {{-- toast de mensaje --}}
     <x-libraries.flux.toast-success />
    
    {{-- buscar juego en api --}}
    <div class="flex gap-2 items-center">
        <flux:modal.trigger name="select-game-api">
            <flux:button size="xs" variant="ghost" icon="plus"></flux:button>
            <flux:label>Buscar Juego</flux:label>
        </flux:modal.trigger>
    </div>

    {{-- formulario completo --}}
    <div class="space-y-2">
        <flux:input type="text" label="Nombre" wire:model="title" placeholder="Nombre del juego" autofocus/>
        <flux:input type="text" label="Nombre Original" wire:model="original_title" placeholder="Nombre del juego original" />
        <flux:textarea
            label="Sinopsis"
            placeholder="Coloque la sinopsis"
            wire:model="synopsis"
            rows="6"
        />
        <div class="grid grid-cols-2 gap-1">
            <flux:input type="number" max="2999" min="1" label="Año de publicacion" wire:model="release_date"/>
        </div>

        <flux:input type="text" label="Link de portada" wire:model="cover_image_url" placeholder="Pegue el link de una imagen"/>
        @if ($this->cover_image_url)
            <div>
                <img src="{{ $this->cover_image_url }}" alt="Portada del libro" class="w-32 h-auto object-cover rounded">
            </div>            
        @endif

        <div class="grid grid-cols-2 gap-1 my-5">
            <flux:field variant="inline" class="flex items-center">
                <flux:checkbox wire:model="is_favorite" />

                <flux:label>Favorito? ❤️</flux:label>

                <flux:error name="is_favorite" />
            </flux:field>
            <flux:field variant="inline" class="flex items-center">
                <flux:checkbox wire:model="is_abandonated" />

                <flux:label>Abandonado? 🚫</flux:label>

                <flux:error name="is_abandonated" />
            </flux:field>
        </div>

        <div class="mt-5">
            <flux:radio.group wire:model="rating">
                <flux:radio value="0" label="Sin valoracion" checked />
                <flux:radio value="1" label="⭐" />
                <flux:radio value="2" label="⭐⭐" />
                <flux:radio value="3" label="⭐⭐⭐" />
                <flux:radio value="4" label="⭐⭐⭐⭐" />
                <flux:radio value="5" label="⭐⭐⭐⭐⭐" />
            </flux:radio.group>
        </div>

        @if ($game)
            <div class="flex gap-2 items-center">
                <flux:button wire:click="modalPlayed" class="mt-1" size="sm" variant="ghost" color="purple" icon="plus" type="submit"></flux:button>
                <flux:separator text="jugados" />
            </div>
        @else
            <div class="grid grid-cols-2 gap-1">
                <flux:input wire:model='start_played' type="date" max="2999-12-31" label="Inicio de juegada" />
                <flux:input wire:model='end_played' type="date" max="2999-12-31" label="Fin de juegada" />
            </div>
        @endif

        @if($game)
            @foreach ($game->playeds as $item)
            <div class="flex items-start justify-between">
                <div class="px-3 border-l-4 border-purple-800">
                   @if ($item->end_played)
                        <p class="mb-2 text-xs sm:text-base text-gray-800 dark:text-gray-300 ">{{ \Carbon\Carbon::parse($item->start_played)->format('Y-m-d') }} - {{ \Carbon\Carbon::parse($item->end_played)->format('Y-m-d') }} en {{ \Carbon\Carbon::parse($item->start_played)->diffInDays($item->end_played) }} dias</p>
                    @else
                        <p class="mb-2 text-xs sm:text-base text-gray-800 dark:text-gray-300 ">{{ \Carbon\Carbon::parse($item->start_played)->format('Y-m-d') }} Jugando...</p>
                    @endif
                </div>

                <flux:button wire:click="deletePlayed({{ $item->id }})" class="ml-3 text-gray-400 hover:text-red-500 transition" size="sm" variant="ghost" color="purple" type="submit">✕</flux:button>
            </div>
            @endforeach
        @endif

        <div>
            <div class="flex items-center mb-1">
                <flux:label>Categorias {{ count($selectedGamesCategories) }}</flux:label>
            </div>
            <flux:checkbox.group wire:model.live="selectedGamesCategories">
                <div class="grid grid-cols-2 md:grid-cols-3 h-max-96 overflow-y-scroll space-y-1">
                    @foreach ($this->categories() as $item)
                        <flux:checkbox label="{{ $item->name }}" value="{{ $item->id }}" />
                    @endforeach
                </div>
            </flux:checkbox.group>
        </div>
        @if ($this->categories_recommended)
            <p class="text-xs italic">Recomendado: {{ implode(', ', $this->categories_recommended) }}</p>
        @endif

        <div class="grid grid-cols-12 gap-1">
            <div class="col-span-10 space-y-1">
                <div class="flex items-center gap-1">
                    <flux:modal.trigger name="add-collection">
                        <flux:button size="xs" variant="ghost" icon="plus"></flux:button>
                    </flux:modal.trigger>
                    <flux:label>Saga</flux:label>
                </div>
                <flux:select wire:model="selectedGameCollections">
                    <option value="">Seleccionar saga</option>
                    @foreach ($this->collections() as $item)
                        <option value="{{ $item->id }}">{{ $item->name }}</option>
                    @endforeach
                </flux:select>
            </div>
            <div class="col-span-2 space-y-1">
                <div>
                    <flux:label>N° saga</flux:label>
                </div>
                <flux:input type="number" max="2999" min="0" step="0.1" wire:model="number_collection"/>
            </div>
        </div>

        <div class="flex items-center gap-1">
            <flux:modal.trigger name="add-subject">
                <flux:button size="xs" variant="ghost" icon="plus"></flux:button>
                <flux:label>Plataforma(s) {{ count($selectedGamePlatforms) }}</flux:label>
            </flux:modal.trigger>
        </div>
 
        <div class="col-span-12 sm:col-span-6">
            <x-libraries.flux.select-multiple
                model="platform" 
                relation="platforms" 
                wire:model="selectedGamePlatforms" 
                :items="$this->platforms()"
                name_input="platform_search"
            />
        </div>

        @if ($this->platforms_recommended)
            {{-- <p class="text-xs italic">Recomendado: {{ implode(', ', $this->platforms_recommended) }}</p> --}}
            <p class="text-xs italic">Recomendado:</p>
            @foreach($this->platforms_recommended as $p)
                <span class="text-xs italic">
                    {{ $p['platform']['name'] }} - 
                </span>
            @endforeach
        @endif

        <div class="flex items-center gap-1">
            <flux:modal.trigger name="add-subject">
                <flux:button size="xs" variant="ghost" icon="plus"></flux:button>
                <flux:label>Desarrollador(es) {{ count($selectedGameSubjects) }}</flux:label>
            </flux:modal.trigger>
        </div>
 
        <div class="col-span-12 sm:col-span-6">
            <x-libraries.flux.select-multiple
                model="subject" 
                relation="subjects" 
                wire:model="selectedGameSubjects" 
                :items="$this->subjects()"
                name_input="desarollador_search"
            />
        </div>

        <div>
            @foreach ($this->devs_recommended as $subject_recommended)
                <span class="italic text-xs hover:underline cursor-pointer"  wire:click="selectSubjectGame('{{ $subject_recommended }}')">{{ $subject_recommended }}</span>
            @endforeach
        </div>

        <flux:label>Etiquetas</flux:label>
        <flux:input.group>
            <flux:input type="text" wire:model="newTag" wire:keydown.period.prevent="addTag('selectedGameTags')" placeholder="Agregue etiquetas" />
            <flux:button wire:click="addTag('selectedGameTags')" icon="plus">Agregar</flux:button>
        </flux:input.group>

        <div class="flex gap-2 mt-2 w-full flex-wrap">
            @foreach($selectedGameTags as $index => $tag)
                <flux:badge size="sm" color="purple">
                    <button class="mr-2" wire:click="removeTag('selectedGameTags', {{ $index }})">
                        x
                    </button>
                    #{{ $tag }}
                </flux:badge>
            @endforeach
        </div>

        <flux:label>Resumen personal</flux:label>
        <x-libraries.quill-textarea-form
            id_quill="editor_create_summary"
            name="summary"
            height="400"
            placeholder="{{ __('Resumen personal') }}" model="summary"
            model_data="{{ $summary }}"
        />

        <flux:label>Reseña</flux:label>
        <x-libraries.quill-textarea-form
            id_quill="editor_create_notes"
            name="notes"
            height="300"
            placeholder="{{ __('Reseña') }}" model="notes"
            model_data="{{ $notes }}"
        />

        <x-libraries.utilities.errors />

        <flux:button :icon="$game ? 'pencil-square' : 'plus'" wire:click="updateItem">{{ $this->buttonSubmit }}</flux:button>
    </div>

    {{-- modal para agregar coleccion --}}
    <flux:modal name="add-collection" class="md:w-96">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Crear saga</flux:heading>
                <flux:text class="mt-2">Cree una saga que no este en el listado.</flux:text>
            </div>

            <flux:input label="Nombre" placeholder="Nombre de la saga" wire:model="name_collection" autofocus/>

            <div class="flex">
                <flux:spacer />

                <x-libraries.utilities.errors />

                <flux:button wire:click="storeCollection('selectedGameCollections')" variant="primary">Agregar</flux:button>
            </div>
        </div>
    </flux:modal>

    {{-- modal para agregar sujeto --}}
    <flux:modal name="add-subject" class="md:w-96">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Crear desarrollador</flux:heading>
                <flux:text class="mt-2">Cree un desarrollador que no este en el listado.</flux:text>
            </div>

            <flux:input label="Nombre" placeholder="Nombre del desarrollador" wire:model="name_subject" autofocus/>

            <div class="flex">
                <flux:spacer />

                <x-libraries.utilities.errors />

                <flux:button wire:click="storeSubject('selectedGameSubjects')" variant="primary">Agregar</flux:button>
            </div>
        </div>
    </flux:modal>

    {{-- modales para agrear y eliminar jugadas --}}
    <flux:modal name="add-played" class="md:w-96">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">jugado</flux:heading>
                <flux:text class="mt-2">Agregue la fecha jugada.</flux:text>
            </div>
            <div class="grid grid-cols-2 gap-1">
                <flux:input wire:model='start_played' type="date" max="2999-12-31" label="Inicio de jugada" />
                <flux:input wire:model='end_played' type="date" max="2999-12-31" label="Fin de jugada" />
            </div>
            <div class="flex gap-2">
                <flux:spacer />

                <flux:modal.close>
                    <flux:button variant="ghost">Cancelar</flux:button>
                </flux:modal.close>

                <flux:button wire:click="addPlayed" type="submit" variant="primary">Editar</flux:button>
            </div>
        </div>
    </flux:modal>
    <flux:modal name="delete-played" class="min-w-[22rem]">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Eliminar</flux:heading>

                <flux:text class="mt-2">
                    <p>Desea eliminar esta jugada?.</p>
                    <p>Esta accion no puede revertirse.</p>
                </flux:text>
            </div>

            <div class="flex gap-2">
                <flux:spacer />

                <flux:modal.close>
                    <flux:button variant="ghost">Cancelar</flux:button>
                </flux:modal.close>

                <flux:button wire:click="destroyPlayed" type="submit" variant="danger">Borrar</flux:button>
            </div>
        </div>
    </flux:modal>


    {{-- modales seleccionar juegos en api --}}
    <flux:modal name="select-game-api" class="md:w-96">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Buscar Juego</flux:heading>
                <flux:text class="mt-2">Busque los datos de una juego.</flux:text>
            </div>

            <div class="grid gap-1">
                <div>
                    <flux:input.group>
                        <flux:input 
                            wire:model.live.debounce.500ms="searchGame"
                            placeholder="Buscar película..."
                        />
                        <flux:button wire:click="searchGames" icon="magnifying-glass"></flux:button>
                    </flux:input.group>
                <div class="space-y-2 mt-4">

                    @foreach($results as $item)
                        <div 
                            wire:click="selectGame({{ $item['id'] }})"
                            class="flex gap-3 p-2 cursor-pointer hover:bg-zinc-100 dark:hover:bg-zinc-800 rounded"
                        >
                            <img 
                                src="{{ $item['background_image'] }}" 
                                class="w-12 h-16 object-cover rounded"
                            />

                            <div>
                                <div class="font-semibold">
                                    {{ $item['name'] }}
                                </div>

                                <div class="text-xs text-zinc-500">
                                    {{ $item['released'] ?? '' }}
                                </div>
                            </div>
                        </div>
                    @endforeach

                </div>
                
                <div class="flex gap-2">
                    <flux:spacer />
    
                    <flux:modal.close>
                        <flux:button variant="ghost">Cancelar</flux:button>
                    </flux:modal.close>
                </div>
            </div>
      

        </div>
    </flux:modal>
</div>