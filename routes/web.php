<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');
    
    // Seccion sujetos
    Route::livewire('subjects', 'pages::page.subjects.subjects-index')->name('subjects.index');
    Route::livewire('subjects/create', 'pages::page.subjects.subjects-create')->name('subjects.create');
    Route::livewire('subjects/{subjectUuid}/edit', 'pages::page.subjects.subjects-edit')->name('subjects.edit');
    Route::livewire('subjects/{subjectUuid}/show', 'pages::page.subjects.subjects-show')->name('subjects.show');
    
    // Seccion colecciones
    Route::livewire('collections', 'pages::page.collections.collections-index')->name('collections.index');
    Route::livewire('collections/create', 'pages::page.collections.collections-create')->name('collections.create');
    Route::livewire('collections/{collectionUuid}/edit', 'pages::page.collections.collections-edit')->name('collections.edit');
    Route::livewire('collections/{collectionUuid}/show', 'pages::page.collections.collections-show')->name('collections.show');
    
    // Seccion generos de libros
    Route::livewire('book-genres', 'pages::page.book-genres.book-genres-index')->name('book-genres.index');
    Route::livewire('book-genres/create', 'pages::page.book-genres.book-genres-create')->name('book-genres.create');
    Route::livewire('book-genres/{bookGenreUuid}/edit', 'pages::page.book-genres.book-genres-edit')->name('book-genres.edit');
    Route::livewire('book-genres/{bookGenreUuid}/show', 'pages::page.book-genres.book-genres-show')->name('book-genres.show');
    
    // Seccion libros
    Route::livewire('books', 'pages::page.books.books-index')->name('books.index');
    Route::livewire('books_library', 'pages::page.books.books-library')->name('books_library.index');
    Route::livewire('books_data', 'pages::page.books.books-data')->name('books_data.index');
    Route::livewire('books_incomplete', 'pages::page.books.books-incomplete')->name('books_incomplete.index');
    Route::livewire('books/create', 'pages::page.books.books-create')->name('books.create');
    Route::livewire('books/{bookUuid}/edit', 'pages::page.books.books-edit')->name('books.edit');
    Route::livewire('books/{bookUuid}/show', 'pages::page.books.books-show')->name('books.show');
    
    // Seccion libros
    Route::livewire('diaries', 'pages::page.diaries.diaries-index')->name('diaries.index');
    Route::livewire('diaries/create/{templateUuid?}', 'pages::page.diaries.diaries-create')->name('diaries.create');
    Route::livewire('diaries/{diaryUuid}/edit', 'pages::page.diaries.diaries-edit')->name('diaries.edit');
    Route::livewire('diaries/{diaryUuid}/show', 'pages::page.diaries.diaries-show')->name('diaries.show');
});

require __DIR__.'/settings.php';
