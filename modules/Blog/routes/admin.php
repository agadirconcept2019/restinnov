<?php

use App\Modules\Blog\Http\Controllers\Admin\CategoryController;
use App\Modules\Blog\Http\Controllers\Admin\PostController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth'])->prefix('admin/blog')->name('admin.blog.')->group(function () {
    Route::get('/posts', [PostController::class, 'index'])->middleware('permission:blog.posts.view')->name('posts.index');
    Route::get('/posts/create', [PostController::class, 'create'])->middleware('permission:blog.posts.create')->name('posts.create');
    Route::post('/posts', [PostController::class, 'store'])->middleware('permission:blog.posts.create')->name('posts.store');
    Route::post('/posts/bulk', [PostController::class, 'bulk'])->middleware('permission:blog.posts.publish')->name('posts.bulk');
    Route::get('/posts/{post}/edit', [PostController::class, 'edit'])->middleware('permission:blog.posts.update')->name('posts.edit');
    Route::put('/posts/{post}', [PostController::class, 'update'])->middleware('permission:blog.posts.update')->name('posts.update');
    Route::delete('/posts/{post}', [PostController::class, 'destroy'])->middleware('permission:blog.posts.delete')->name('posts.destroy');

    Route::get('/categories', [CategoryController::class, 'index'])->middleware('permission:blog.categories.manage')->name('categories.index');
    Route::post('/categories', [CategoryController::class, 'store'])->middleware('permission:blog.categories.manage')->name('categories.store');
    Route::put('/categories/{category}', [CategoryController::class, 'update'])->middleware('permission:blog.categories.manage')->name('categories.update');
    Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])->middleware('permission:blog.categories.manage')->name('categories.destroy');
});
