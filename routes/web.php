<?php

use App\Livewire\Pages\Dashboard;
use App\Livewire\Pages\IssueCreate;
use App\Livewire\Pages\IssueShow;
use Illuminate\Support\Facades\Route;

Route::get('/', Dashboard::class)->name('dashboard');
Route::get('/issues/new', IssueCreate::class)->name('issues.create');
Route::get('/issues/{issue}', IssueShow::class)->name('issues.show');
