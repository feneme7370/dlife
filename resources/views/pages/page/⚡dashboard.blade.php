<?php

use App\Models\Page\BookRead;
use App\Models\Page\Diary;
use App\Models\Page\MovieView;
use App\Models\Page\Recipe;
use App\Models\Page\SerieView;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

new class extends Component
{
    public $titlePage = 'Dashboard';
    public $subtitlePage = 'Pantalla inicial';

    public $moviesYear;
    public $moviesTotal;
    public $seriesYear;
    public $seriesTotal;
    public $booksYear;
    public $booksTotal;
    public $diariesYear;
    public $diariesTotal;
    public $recipesTotal;

    public $quoteContent;
    public $quoteAuthor;

    public function mount(){
        $movies = MovieView::where('user_id', Auth::id());
        $this->moviesTotal = $movies->whereNotNull('end_view')->count();
        $this->moviesYear = $movies->whereNotNull('end_view')->whereYear('end_view', now()->year)->count();

        $series = SerieView::where('user_id', Auth::id());
        $this->seriesTotal = $series->whereNotNull('end_view')->count();
        $this->seriesYear = $series->whereNotNull('end_view')->whereYear('end_view', now()->year)->count();

        $books = BookRead::where('user_id', Auth::id());
        $this->booksTotal = $books->whereNotNull('end_read')->count();
        $this->booksYear = $books->whereNotNull('end_read')->whereYear('end_read', now()->year)->count();

        $diaries = Diary::where('user_id', Auth::id());
        $this->diariesTotal = $diaries->count();
        $this->diariesYear = $diaries->whereYear('day', now()->year)->count();

        $this->recipesTotal = Recipe::where('user_id', Auth::id())->count();

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
    <div class="mb-3">
        <div container class="mb-1 space-y-1">
            <flux:heading size="xl" level="1">
                {{ $this->titlePage }}
            </flux:heading>
            <flux:text class="text-base">{{ $this->subtitlePage }}</flux:text>
    
            <flux:breadcrumbs>
                <flux:breadcrumbs.item href="{{ route('dashboard') }}">Dashboard</flux:breadcrumbs.item>
            </flux:breadcrumbs>
    
            <flux:separator variant="subtle" />
        </div>
    </div>

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
                <flux:text class="mt-2">Vistas este año ({{ $moviesYear }}).</flux:text>
                <flux:text class="mt-2">Vistas en total ({{ $moviesTotal }}).</flux:text>
            </flux:card>
        </a>
        <a href="{{ route('series_library.index') }}" aria-label="Latest on our blog">
            <flux:card size="sm" class="hover:bg-zinc-50 dark:hover:bg-zinc-700">
                <flux:heading class="flex items-center gap-2">Series <flux:icon name="arrow-up-right" class="ml-auto text-zinc-400" variant="micro" /></flux:heading>
                <flux:text class="mt-2">Vistas este año ({{ $seriesYear }}).</flux:text>
                <flux:text class="mt-2">Vistas en total ({{ $seriesTotal }}).</flux:text>
            </flux:card>
        </a>
        <a href="{{ route('books_library.index') }}" aria-label="Latest on our blog">
            <flux:card size="sm" class="hover:bg-zinc-50 dark:hover:bg-zinc-700">
                <flux:heading class="flex items-center gap-2">Libros <flux:icon name="arrow-up-right" class="ml-auto text-zinc-400" variant="micro" /></flux:heading>
                <flux:text class="mt-2">Leidos este año ({{ $booksYear }}).</flux:text>
                <flux:text class="mt-2">Leidos en total ({{ $booksTotal }}).</flux:text>
            </flux:card>
        </a>
        <a href="{{ route('diaries.index') }}" aria-label="Latest on our blog">
            <flux:card size="sm" class="hover:bg-zinc-50 dark:hover:bg-zinc-700">
                <flux:heading class="flex items-center gap-2">Diario <flux:icon name="arrow-up-right" class="ml-auto text-zinc-400" variant="micro" /></flux:heading>
                <flux:text class="mt-2">Escrito este año ({{ $diariesYear }} / 365).</flux:text>
                <flux:text class="mt-2">Escrito en total ({{ $diariesTotal }}).</flux:text>
            </flux:card>
        </a>
        <a href="{{ route('recipes.index') }}" aria-label="Latest on our blog">
            <flux:card size="sm" class="hover:bg-zinc-50 dark:hover:bg-zinc-700">
                <flux:heading class="flex items-center gap-2">Recetas <flux:icon name="arrow-up-right" class="ml-auto text-zinc-400" variant="micro" /></flux:heading>
                <flux:text class="mt-2">Recetas en total ({{ $recipesTotal }}).</flux:text>
            </flux:card>
        </a>
    </div>
</div>