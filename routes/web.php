<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    //////////////////////////////////////////////////////////////////////////////////////////////////////
    // dashboard
    Route::livewire('dashboard', 'pages::page.dashboard')->name('dashboard');

    //////////////////////////////////////////////////////////////////////////////////////////////////////
    // pagina de enlaces
    Route::livewire('asociaciones', 'pages::page.associations.associations-index')->name('associations.index');

    // Seccion sujetos
    Route::livewire('subjects', 'pages::page.associations.subjects.subjects-index')->name('subjects.index');
    Route::livewire('subjects/create', 'pages::page.associations.subjects.subjects-edit')->name('subjects.create');
    Route::livewire('subjects/{subjectUuid}/edit', 'pages::page.associations.subjects.subjects-edit')->name('subjects.edit');
    Route::livewire('subjects/{subjectUuid}/show', 'pages::page.associations.subjects.subjects-show')->name('subjects.show');
    
    // Seccion colecciones
    Route::livewire('collections', 'pages::page.associations.collections.collections-index')->name('collections.index');
    Route::livewire('collections/create', 'pages::page.associations.collections.collections-edit')->name('collections.create');
    Route::livewire('collections/{collectionUuid}/edit', 'pages::page.associations.collections.collections-edit')->name('collections.edit');
    Route::livewire('collections/{collectionUuid}/show', 'pages::page.associations.collections.collections-show')->name('collections.show');
    
    // Seccion etiquetas
    Route::livewire('tags', 'pages::page.associations.tags.tags-index')->name('tags.index');
    Route::livewire('tags/create', 'pages::page.associations.tags.tags-edit')->name('tags.create');
    Route::livewire('tags/{tagUuid}/edit', 'pages::page.associations.tags.tags-edit')->name('tags.edit');
    Route::livewire('tags/{tagUuid}/show', 'pages::page.associations.tags.tags-show')->name('tags.show');
    
    // Seccion generos
    Route::livewire('genres', 'pages::page.associations.genres.genres-index')->name('genres.index');
    Route::livewire('genres/create', 'pages::page.associations.genres.genres-edit')->name('genres.create');
    Route::livewire('genres/{genreUuid}/edit', 'pages::page.associations.genres.genres-edit')->name('genres.edit');
    Route::livewire('genres/{genreUuid}/show', 'pages::page.associations.genres.genres-show')->name('genres.show');
    
    // Seccion categorias
    Route::livewire('categories', 'pages::page.associations.categories.categories-index')->name('categories.index');
    Route::livewire('categories/create', 'pages::page.associations.categories.categories-edit')->name('categories.create');
    Route::livewire('categories/{categoryUuid}/edit', 'pages::page.associations.categories.categories-edit')->name('categories.edit');
    Route::livewire('categories/{categoryUuid}/show', 'pages::page.associations.categories.categories-show')->name('categories.show');
    
    // Seccion plataformas
    Route::livewire('platforms', 'pages::page.associations.platforms.platforms-index')->name('platforms.index');
    Route::livewire('platforms/create', 'pages::page.associations.platforms.platforms-edit')->name('platforms.create');
    Route::livewire('platforms/{platformUuid}/edit', 'pages::page.associations.platforms.platforms-edit')->name('platforms.edit');
    Route::livewire('platforms/{platformUuid}/show', 'pages::page.associations.platforms.platforms-show')->name('platforms.show');

    //////////////////////////////////////////////////////////////////////////////////////////////////////
    // Seccion libros
    Route::livewire('books', 'pages::page.books.books-index')->name('books.index');
    Route::livewire('books_library', 'pages::page.books.books-library')->name('books_library.index');
    Route::livewire('books_library_manga', 'pages::page.books.books-library-manga')->name('books_library_manga.index');
    Route::livewire('books_data', 'pages::page.books.books-data')->name('books_data.index');
    Route::livewire('books_data_manga', 'pages::page.books.books-data-manga')->name('books_data_manga.index');
    Route::livewire('books_incomplete', 'pages::page.books.books-incomplete')->name('books_incomplete.index');
    Route::livewire('books/create', 'pages::page.books.books-edit')->name('books.create');
    Route::livewire('books/{bookUuid}/edit', 'pages::page.books.books-edit')->name('books.edit');
    Route::livewire('books/{bookUuid}/show', 'pages::page.books.books-show')->name('books.show');
    
    //////////////////////////////////////////////////////////////////////////////////////////////////////
    // Seccion diario
    Route::livewire('diaries/show/all', 'pages::page.diaries.diaries-all')->name('diaries.all');
    Route::livewire('diaries', 'pages::page.diaries.diaries-index')->name('diaries.index');
    Route::livewire('diaries/create/{templateUuid?}', 'pages::page.diaries.diaries-edit')->name('diaries.create');
    Route::livewire('diaries/{diaryUuid}/edit', 'pages::page.diaries.diaries-edit')->name('diaries.edit');
    Route::livewire('diaries/{diaryUuid}/show', 'pages::page.diaries.diaries-show')->name('diaries.show');
        
    //////////////////////////////////////////////////////////////////////////////////////////////////////

    // Seccion peliculas
    Route::livewire('movies', 'pages::page.medias.movies.movies-index')->name('movies.index');
    Route::livewire('movies_library', 'pages::page.medias.movies.movies-library')->name('movies_library.index');
    Route::livewire('movies_data', 'pages::page.medias.movies.movies-data')->name('movies_data.index');
    // Route::livewire('movies_incomplete', 'pages::page.medias.movies.movies-incomplete')->name('movies_incomplete.index');
    Route::livewire('movies/create', 'pages::page.medias.movies.movies-edit')->name('movies.create');
    Route::livewire('movies/{movieUuid}/edit', 'pages::page.medias.movies.movies-edit')->name('movies.edit');
    Route::livewire('movies/{movieUuid}/show', 'pages::page.medias.movies.movies-show')->name('movies.show');

    // Seccion series
    Route::livewire('series', 'pages::page.medias.series.series-index')->name('series.index');
    Route::livewire('series_library', 'pages::page.medias.series.series-library')->name('series_library.index');
    Route::livewire('series_data', 'pages::page.medias.series.series-data')->name('series_data.index');
    // Route::livewire('series_incomplete', 'pages::page.medias.series.series-incomplete')->name('series_incomplete.index');
    Route::livewire('series/create', 'pages::page.medias.series.series-edit')->name('series.create');
    Route::livewire('series/{serieUuid}/edit', 'pages::page.medias.series.series-edit')->name('series.edit');
    Route::livewire('series/{serieUuid}/show', 'pages::page.medias.series.series-show')->name('series.show');

    //////////////////////////////////////////////////////////////////////////////////////////////////////

    // Seccion recipes
    Route::livewire('recipes', 'pages::page.recipes.recipes-index')->name('recipes.index');
    Route::livewire('recipes_library', 'pages::page.recipes.recipes-library')->name('recipes_library.index');
    Route::livewire('recipes/create', 'pages::page.recipes.recipes-edit')->name('recipes.create');
    Route::livewire('recipes/{recipeUuid}/edit', 'pages::page.recipes.recipes-edit')->name('recipes.edit');
    Route::livewire('recipes/{recipeUuid}/show', 'pages::page.recipes.recipes-show')->name('recipes.show');

    //////////////////////////////////////////////////////////////////////////////////////////////////////
    // Seccion quotes
    Route::livewire('quotes', 'pages::page.quotes.quotes-index')->name('quotes.index');
    Route::livewire('quotes/create', 'pages::page.quotes.quotes-edit')->name('quotes.create');
    Route::livewire('quotes/{quoteUuid}/edit', 'pages::page.quotes.quotes-edit')->name('quotes.edit');

    //////////////////////////////////////////////////////////////////////////////////////////////////////
    // Seccion blogs
    Route::livewire('blogs', 'pages::page.blogs.blogs-index')->name('blogs.index');
    Route::livewire('blogs/create', 'pages::page.blogs.blogs-edit')->name('blogs.create');
    Route::livewire('blogs/{blogUuid}/edit', 'pages::page.blogs.blogs-edit')->name('blogs.edit');
    Route::livewire('blogs/{blogUuid}/show', 'pages::page.blogs.blogs-show')->name('blogs.show');

    //////////////////////////////////////////////////////////////////////////////////////////////////////
    // Seccion bullets
    Route::livewire('bullets', 'pages::page.bullets.bullets-index')->name('bullets.index');
    Route::livewire('bullets/create', 'pages::page.bullets.bullets-edit')->name('bullets.create');
    Route::livewire('bullets/{bulletUuid}/edit', 'pages::page.bullets.bullets-edit')->name('bullets.edit');
    Route::livewire('bullets/{bulletUuid}/show', 'pages::page.bullets.bullets-show')->name('bullets.show');

    //////////////////////////////////////////////////////////////////////////////////////////////////////
    // Seccion games
    Route::livewire('games', 'pages::page.medias.games.games-index')->name('games.index');
    Route::livewire('games_library', 'pages::page.medias.games.games-library')->name('games_library.index');
    Route::livewire('games_data', 'pages::page.medias.games.games-data')->name('games_data.index');
    // Route::livewire('games_incomplete', 'pages::page.medias.games.games-incomplete')->name('games_incomplete.index');
    Route::livewire('games/create', 'pages::page.medias.games.games-edit')->name('games.create');
    Route::livewire('games/{gameUuid}/edit', 'pages::page.medias.games.games-edit')->name('games.edit');
    Route::livewire('games/{gameUuid}/show', 'pages::page.medias.games.games-show')->name('games.show');
});

require __DIR__.'/settings.php';
