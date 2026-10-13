<?php

use App\Http\Controllers\Admin\LogoutController;
use App\Http\Controllers\Admin\SubscriberExportController;
use App\Livewire\Admin\Articles\Form as ArticleForm;
use App\Livewire\Admin\Articles\Index as ArticleIndex;
use App\Livewire\Admin\Categories\Index as CategoryIndex;
use App\Livewire\Admin\Dashboard;
use App\Livewire\Admin\Login;
use App\Livewire\Admin\Messages\Index as MessageIndex;
use App\Livewire\Admin\Settings;
use App\Livewire\Admin\Subscribers\Index as SubscriberIndex;
use Illuminate\Support\Facades\Route;

Route::view('/', 'home')->name('home');
Route::view('/articles', 'placeholder', ['title' => 'Articles'])->name('articles.index');
Route::view('/about', 'placeholder', ['title' => 'About'])->name('about');
Route::view('/contact', 'placeholder', ['title' => 'Contact'])->name('contact');

Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('login', Login::class)->middleware('guest')->name('login');

    Route::middleware(['auth', 'can:access-admin'])->group(function () {
        Route::post('logout', LogoutController::class)->name('logout');
        Route::get('/', Dashboard::class)->name('dashboard');

        Route::get('articles', ArticleIndex::class)->name('articles.index');
        Route::get('articles/create', ArticleForm::class)->name('articles.create');
        Route::get('articles/{article:id}/edit', ArticleForm::class)->name('articles.edit');

        Route::get('categories', CategoryIndex::class)->name('categories.index');
        Route::get('subscribers', SubscriberIndex::class)->name('subscribers.index');
        Route::get('subscribers/export', SubscriberExportController::class)->name('subscribers.export');
        Route::get('messages', MessageIndex::class)->name('messages.index');
        Route::get('settings', Settings::class)->name('settings');
    });
});
