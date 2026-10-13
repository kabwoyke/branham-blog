<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'home')->name('home');
Route::view('/articles', 'placeholder', ['title' => 'Articles'])->name('articles.index');
Route::view('/about', 'placeholder', ['title' => 'About'])->name('about');
Route::view('/contact', 'placeholder', ['title' => 'Contact'])->name('contact');
