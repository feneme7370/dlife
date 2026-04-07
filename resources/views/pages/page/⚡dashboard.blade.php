<?php

use App\Models\Page\Blog;
use App\Models\Page\BookRead;
use App\Models\Page\Diary;
use App\Models\Page\GamePlayed;
use App\Models\Page\MovieView;
use App\Models\Page\Recipe;
use App\Models\Page\SerieView;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

new class extends Component
{
    public $titlePage = 'Dashboard';
    public $subtitlePage = 'Pantalla inicial';

    public $moviesYear;
    public $moviesTotal;
    public $seriesYear;
    public $seriesTotal;
    public $gamesYear;
    public $gamesTotal;
    public $booksYear;
    public $booksTotal;
    public $mangasYear;
    public $mangasTotal;
    public $diariesYear;
    public $diariesTotal;
    public $recipesTotal;
    public $blogsTotal;
    public $bulletsTotal;

    public $quoteContent;
    public $quoteAuthor;

    public $data;

    public function mount(){
        $movies = MovieView::where('user_id', Auth::id());
        $this->moviesTotal = $movies->whereNotNull('end_view')->count();
        $this->moviesYear = $movies->whereNotNull('end_view')->whereYear('end_view', now()->year)->count();

        $series = SerieView::where('user_id', Auth::id());
        $this->seriesTotal = $series->whereNotNull('end_view')->count();
        $this->seriesYear = $series->whereNotNull('end_view')->whereYear('end_view', now()->year)->count();

        $games = GamePlayed::where('user_id', Auth::id());
        $this->gamesTotal = $games->whereNotNull('end_played')->count();
        $this->gamesYear = $games->whereNotNull('end_played')->whereYear('end_played', now()->year)->count();

        $books = BookRead::where('user_id', Auth::id());
        $this->booksTotal = $books->whereHas('book', fn($q) => $q->where('type', 1))->whereNotNull('end_read')->count();
        $this->booksYear = $books->whereHas('book', fn($q) => $q->where('type', 1))->whereNotNull('end_read')->whereYear('end_read', now()->year)->count();
        
        $mangas = BookRead::where('user_id', Auth::id());
        $this->mangasTotal = $mangas->whereHas('book', fn($q) => $q->where('type', 2))->whereNotNull('end_read')->count();
        $this->mangasYear = $mangas->whereHas('book', fn($q) => $q->where('type', 2))->whereNotNull('end_read')->whereYear('end_read', now()->year)->count();

        $diaries = Diary::where('user_id', Auth::id());
        $this->diariesTotal = $diaries->count();
        $this->diariesYear = $diaries->whereYear('day', now()->year)->count();

        $this->recipesTotal = Recipe::where('user_id', Auth::id())->count();
        $this->blogsTotal = Blog::where('user_id', Auth::id())->where('entry_type', 'blog')->count();
        $this->bulletsTotal = Blog::where('user_id', Auth::id())->where('entry_type', 'bullet')->count();

        $this->randomQuote();
    }

    public function randomQuote(){
        $quote = \App\Models\Page\Quote::where('user_id', Auth::id())
            ->inRandomOrder()
            ->first();

        if ($quote) {
            $this->quoteContent = $quote->content;
            $this->quoteAuthor = $quote->author;
        }
    }
};
?>
<div>
     {{-- titulo, descripcion y breadcrumbs --}}
    <x-page.partials.title-page 
        :title="$this->titlePage"
        :create-route="'dashboard'"
        icon="home"
        :breadcrumbs="[
            ['label' => $this->titlePage]
        ]"
    />

     {{-- toast de mensaje --}}
     <x-libraries.flux.toast-success />

    <div class="grid grid-cols-2 gap-5">
        <a href="" aria-label="Latest on our blog" class="col-span-2">
            <flux:card size="sm" class="hover:bg-zinc-50 dark:hover:bg-zinc-700">
                <flux:heading class="flex items-center gap-2">Frases <flux:icon name="arrow-path" class="ml-auto text-zinc-400" variant="micro" wire:click="randomQuote"/></flux:heading>
                <flux:text class="mt-2">{{ $quoteContent }}</flux:text>
                @if($quoteAuthor)
                    <p class="text-xs text-gray-600 dark:text-gray-300 mt-2">
                        — {{ $quoteAuthor }}
                    </p>
                @endif
            </flux:card>
        </a>
        <a href="{{ route('movies_library.index') }}" aria-label="Latest on our blog">
            <flux:card size="sm" class="hover:bg-zinc-50 dark:hover:bg-zinc-700">
                <flux:heading class="flex items-center gap-2">Peliculas <flux:icon name="arrow-up-right" class="ml-auto text-zinc-400" variant="micro" /></flux:heading>
                <flux:text class="mt-2">En año ({{ $moviesYear }}).</flux:text>
                <flux:text class="mt-2">Total ({{ $moviesTotal }}).</flux:text>
            </flux:card>
        </a>
        <a href="{{ route('series_library.index') }}" aria-label="Latest on our blog">
            <flux:card size="sm" class="hover:bg-zinc-50 dark:hover:bg-zinc-700">
                <flux:heading class="flex items-center gap-2">Series <flux:icon name="arrow-up-right" class="ml-auto text-zinc-400" variant="micro" /></flux:heading>
                <flux:text class="mt-2">En año ({{ $seriesYear }}).</flux:text>
                <flux:text class="mt-2">Total ({{ $seriesTotal }}).</flux:text>
            </flux:card>
        </a>
        <a href="{{ route('books_library.index') }}" aria-label="Latest on our blog">
            <flux:card size="sm" class="hover:bg-zinc-50 dark:hover:bg-zinc-700">
                <flux:heading class="flex items-center gap-2">Libros <flux:icon name="arrow-up-right" class="ml-auto text-zinc-400" variant="micro" /></flux:heading>
                <flux:text class="mt-2">En año ({{ $booksYear }}).</flux:text>
                <flux:text class="mt-2">Total ({{ $booksTotal }}).</flux:text>
            </flux:card>
        </a>
        <a href="{{ route('books_library.index') }}" aria-label="Latest on our blog">
            <flux:card size="sm" class="hover:bg-zinc-50 dark:hover:bg-zinc-700">
                <flux:heading class="flex items-center gap-2">Mangas <flux:icon name="arrow-up-right" class="ml-auto text-zinc-400" variant="micro" /></flux:heading>
                <flux:text class="mt-2">En año ({{ $mangasYear }}).</flux:text>
                <flux:text class="mt-2">Total ({{ $mangasTotal }}).</flux:text>
            </flux:card>
        </a>
        <a href="{{ route('games_library.index') }}" aria-label="Latest on our blog">
            <flux:card size="sm" class="hover:bg-zinc-50 dark:hover:bg-zinc-700">
                <flux:heading class="flex items-center gap-2">Juegos <flux:icon name="arrow-up-right" class="ml-auto text-zinc-400" variant="micro" /></flux:heading>
                <flux:text class="mt-2">En año ({{ $gamesYear }}).</flux:text>
                <flux:text class="mt-2">Total ({{ $gamesTotal }}).</flux:text>
            </flux:card>
        </a>
        <a href="{{ route('diaries.index') }}" aria-label="Latest on our blog">
            <flux:card size="sm" class="hover:bg-zinc-50 dark:hover:bg-zinc-700">
                <flux:heading class="flex items-center gap-2">Diario <flux:icon name="arrow-up-right" class="ml-auto text-zinc-400" variant="micro" /></flux:heading>
                <flux:text class="mt-2">En año ({{ $diariesYear }} / 365).</flux:text>
                <flux:text class="mt-2">Total ({{ $diariesTotal }}).</flux:text>
            </flux:card>
        </a>
        <a href="{{ route('recipes.index') }}" aria-label="Latest on our blog">
            <flux:card size="sm" class="hover:bg-zinc-50 dark:hover:bg-zinc-700">
                <flux:heading class="flex items-center gap-2">Recetas <flux:icon name="arrow-up-right" class="ml-auto text-zinc-400" variant="micro" /></flux:heading>
                <flux:text class="mt-2">Total ({{ $recipesTotal }}).</flux:text>
            </flux:card>
        </a>
        <a href="{{ route('blogs.index') }}" aria-label="Latest on our blog">
            <flux:card size="sm" class="hover:bg-zinc-50 dark:hover:bg-zinc-700">
                <flux:heading class="flex items-center gap-2">Blogs y Bujos <flux:icon name="arrow-up-right" class="ml-auto text-zinc-400" variant="micro" /></flux:heading>
                <flux:text class="mt-2">Blogs ({{ $blogsTotal }}).</flux:text>
                <flux:text class="mt-2">Bullets ({{ $bulletsTotal }}).</flux:text>
            </flux:card>
        </a>
    </div>

    <div class="mt-32">
        <p>Favorito ❤️</p>
        <p>Abandonado 🚫</p>
        <p>Resumido 🗒️</p>
        <p>Reseñado ✍️</p>
        <p>Visto/leido ✅</p>
        <p>Hashtag #️⃣</p>

        <p>Paginas 📃</p>
        <p>Mensual 📇</p>
        <p>Mensual 📅</p>
        <p>Listado libros 📖</p>

        <p>Valoracion ⭐</p>
        <p>Clasificacion 🏷</p>
        <p>Estadisticas 📊</p>

        <p>Comidas 🍳</p>
        <p>Series 📺</p>
        <p>Peliculas 🎥</p>
        <p>Animes 👾</p>
        <p>Juegos 🕹️</p>
        <p>Libros 📙</p>
    </div>
</div>