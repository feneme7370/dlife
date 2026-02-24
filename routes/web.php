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
    
    // Seccion generos
    Route::livewire('book-genres', 'pages::page.book-genres.book-genres-index')->name('book-genres.index');
    Route::livewire('book-genres/create', 'pages::page.book-genres.book-genres-create')->name('book-genres.create');
    Route::livewire('book-genres/{bookGenreUuid}/edit', 'pages::page.book-genres.book-genres-edit')->name('book-genres.edit');
    Route::livewire('book-genres/{bookGenreUuid}/show', 'pages::page.book-genres.book-genres-show')->name('book-genres.show');
});

require __DIR__.'/settings.php';
