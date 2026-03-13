<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    // dashboard
    Route::livewire('dashboard', 'pages::page.dashboard')->name('dashboard');
    
    // Seccion sujetos
    Route::livewire('subjects', 'pages::page.subjects.subjects-index')->name('subjects.index');
    Route::livewire('subjects/create', 'pages::page.subjects.subjects-edit')->name('subjects.create');
    Route::livewire('subjects/{subjectUuid}/edit', 'pages::page.subjects.subjects-edit')->name('subjects.edit');
    Route::livewire('subjects/{subjectUuid}/show', 'pages::page.subjects.subjects-show')->name('subjects.show');
    
    // Seccion colecciones
    Route::livewire('collections', 'pages::page.collections.collections-index')->name('collections.index');
    Route::livewire('collections/create', 'pages::page.collections.collections-edit')->name('collections.create');
    Route::livewire('collections/{collectionUuid}/edit', 'pages::page.collections.collections-edit')->name('collections.edit');
    Route::livewire('collections/{collectionUuid}/show', 'pages::page.collections.collections-show')->name('collections.show');
    
    // Seccion generos de libros
    Route::livewire('book-genres', 'pages::page.books.book-genres.book-genres-index')->name('book-genres.index');
    Route::livewire('book-genres/create', 'pages::page.books.book-genres.book-genres-edit')->name('book-genres.create');
    Route::livewire('book-genres/{bookGenreUuid}/edit', 'pages::page.books.book-genres.book-genres-edit')->name('book-genres.edit');
    Route::livewire('book-genres/{bookGenreUuid}/show', 'pages::page.books.book-genres.book-genres-show')->name('book-genres.show');
    
    // Seccion libros
    Route::livewire('books', 'pages::page.books.books-index')->name('books.index');
    Route::livewire('books_library', 'pages::page.books.books-library')->name('books_library.index');
    Route::livewire('books_data', 'pages::page.books.books-data')->name('books_data.index');
    Route::livewire('books_incomplete', 'pages::page.books.books-incomplete')->name('books_incomplete.index');
    Route::livewire('books/create', 'pages::page.books.books-create')->name('books.create');
    Route::livewire('books/{bookUuid}/edit', 'pages::page.books.books-edit')->name('books.edit');
    Route::livewire('books/{bookUuid}/show', 'pages::page.books.books-show')->name('books.show');
    
    //////////////////////////////////////////////////////////////////////////////////////////////////////
    // Seccion diario
    Route::livewire('diaries', 'pages::page.diaries.diaries-index')->name('diaries.index');
    Route::livewire('diaries/create/{templateUuid?}', 'pages::page.diaries.diaries-create')->name('diaries.create');
    Route::livewire('diaries/{diaryUuid}/edit', 'pages::page.diaries.diaries-edit')->name('diaries.edit');
    Route::livewire('diaries/{diaryUuid}/show', 'pages::page.diaries.diaries-show')->name('diaries.show');
    
    // Seccion tags de diarios
    Route::livewire('dtags', 'pages::page.diaries.dtags.dtags-index')->name('dtags.index');
    Route::livewire('dtags/create', 'pages::page.diaries.dtags.dtags-edit')->name('dtags.create');
    Route::livewire('dtags/{dtagUuid}/edit', 'pages::page.diaries.dtags.dtags-edit')->name('dtags.edit');
    Route::livewire('dtags/{dtagUuid}/show', 'pages::page.diaries.dtags.dtags-show')->name('dtags.show');
    
    // Seccion categories de diarios
    Route::livewire('dcategories', 'pages::page.diaries.dcategories.dcategories-index')->name('dcategories.index');
    Route::livewire('dcategories/create', 'pages::page.diaries.dcategories.dcategories-edit')->name('dcategories.create');
    Route::livewire('dcategories/{dcategoryUuid}/edit', 'pages::page.diaries.dcategories.dcategories-edit')->name('dcategories.edit');
    Route::livewire('dcategories/{dcategoryUuid}/show', 'pages::page.diaries.dcategories.dcategories-show')->name('dcategories.show');
    
    //////////////////////////////////////////////////////////////////////////////////////////////////////

    // Seccion peliculas
    Route::livewire('movies', 'pages::page.medias.movies.movies-index')->name('movies.index');
    Route::livewire('movies_library', 'pages::page.medias.movies.movies-library')->name('movies_library.index');
    Route::livewire('movies_data', 'pages::page.medias.movies.movies-data')->name('movies_data.index');
    // Route::livewire('movies_incomplete', 'pages::page.medias.movies.movies-incomplete')->name('movies_incomplete.index');
    Route::livewire('movies/create', 'pages::page.medias.movies.movies-create')->name('movies.create');
    Route::livewire('movies/{movieUuid}/edit', 'pages::page.medias.movies.movies-edit')->name('movies.edit');
    Route::livewire('movies/{movieUuid}/show', 'pages::page.medias.movies.movies-show')->name('movies.show');

    // Seccion series
    Route::livewire('series', 'pages::page.medias.series.series-index')->name('series.index');
    Route::livewire('series_library', 'pages::page.medias.series.series-library')->name('series_library.index');
    Route::livewire('series_data', 'pages::page.medias.series.series-data')->name('series_data.index');
    // Route::livewire('series_incomplete', 'pages::page.medias.series.series-incomplete')->name('series_incomplete.index');
    Route::livewire('series/create', 'pages::page.medias.series.series-create')->name('series.create');
    Route::livewire('series/{serieUuid}/edit', 'pages::page.medias.series.series-edit')->name('series.edit');
    Route::livewire('series/{serieUuid}/show', 'pages::page.medias.series.series-show')->name('series.show');

    // Seccion genres de media
    Route::livewire('mgenres', 'pages::page.medias.mgenres.mgenres-index')->name('mgenres.index');
    Route::livewire('mgenres/create', 'pages::page.medias.mgenres.mgenres-edit')->name('mgenres.create');
    Route::livewire('mgenres/{mgenreUuid}/edit', 'pages::page.medias.mgenres.mgenres-edit')->name('mgenres.edit');
    
    // Seccion tags de media
    Route::livewire('mtags', 'pages::page.medias.mtags.mtags-index')->name('mtags.index');
    Route::livewire('mtags/create', 'pages::page.medias.mtags.mtags-edit')->name('mtags.create');
    Route::livewire('mtags/{mtagUuid}/edit', 'pages::page.medias.mtags.mtags-edit')->name('mtags.edit');

    //////////////////////////////////////////////////////////////////////////////////////////////////////

    // Seccion recipes
    Route::livewire('recipes', 'pages::page.recipes.recipes-index')->name('recipes.index');
    Route::livewire('recipes_library', 'pages::page.recipes.recipes-library')->name('recipes_library.index');
    Route::livewire('recipes/create', 'pages::page.recipes.recipes-edit')->name('recipes.create');
    Route::livewire('recipes/{recipeUuid}/edit', 'pages::page.recipes.recipes-edit')->name('recipes.edit');
    Route::livewire('recipes/{recipeUuid}/show', 'pages::page.recipes.recipes-show')->name('recipes.show');

    // Seccion tags de recipes
    Route::livewire('rtags', 'pages::page.recipes.rtags.rtags-index')->name('rtags.index');
    Route::livewire('rtags/create', 'pages::page.recipes.rtags.rtags-edit')->name('rtags.create');
    Route::livewire('rtags/{rtagUuid}/edit', 'pages::page.recipes.rtags.rtags-edit')->name('rtags.edit');

    // Seccion tags de recipes
    Route::livewire('rcategories', 'pages::page.recipes.rcategories.rcategories-index')->name('rcategories.index');
    Route::livewire('rcategories/create', 'pages::page.recipes.rcategories.rcategories-edit')->name('rcategories.create');
    Route::livewire('rcategories/{rcategoryUuid}/edit', 'pages::page.recipes.rcategories.rcategories-edit')->name('rcategories.edit');

    //////////////////////////////////////////////////////////////////////////////////////////////////////
    // Seccion tags de recipes
    Route::livewire('quotes', 'pages::page.quotes.quotes-index')->name('quotes.index');
    Route::livewire('quotes/create', 'pages::page.quotes.quotes-edit')->name('quotes.create');
    Route::livewire('quotes/{quoteUuid}/edit', 'pages::page.quotes.quotes-edit')->name('quotes.edit');

    //////////////////////////////////////////////////////////////////////////////////////////////////////

    // Seccion recipes
    Route::livewire('blogs', 'pages::page.blogs.blogs-index')->name('blogs.index');
    Route::livewire('blogs/create', 'pages::page.blogs.blogs-edit')->name('blogs.create');
    Route::livewire('blogs/{blogUuid}/edit', 'pages::page.blogs.blogs-edit')->name('blogs.edit');
    Route::livewire('blogs/{blogUuid}/show', 'pages::page.blogs.blogs-show')->name('blogs.show');

    // Seccion tags de blogs
    Route::livewire('bltags', 'pages::page.blogs.bltags.bltags-index')->name('bltags.index');
    Route::livewire('bltags/create', 'pages::page.blogs.bltags.bltags-edit')->name('bltags.create');
    Route::livewire('bltags/{bltagUuid}/edit', 'pages::page.blogs.bltags.bltags-edit')->name('bltags.edit');
});

require __DIR__.'/settings.php';
