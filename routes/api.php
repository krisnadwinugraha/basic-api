<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Dashboard\DashboardController;
use App\Http\Controllers\MasterData\AdminController;
use App\Http\Controllers\MasterData\MemberController;
use App\Http\Controllers\Role\RoleController;
use App\Http\Controllers\Content\ArticleController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
Route::post('auth/register', RegisterController::class);
Route::post('auth/login', LoginController::class);

Route::group(['middleware' => ['auth:sanctum']], function () {
    Route::get('/profile', function(Request $request) {
        return auth()->user();
    });
    Route::get('/dashboard', DashboardController::class);
    Route::get('/role', RoleController::class);
    Route::post('/logout', LogoutController::class);

    Route::get('/admin', [AdminController::class, 'index'])
            ->name('index')
            ->middleware('can:users-list');
    Route::post('/admin', [AdminController::class, 'store'])
            ->name('store')
            ->middleware('can:users-create');
    Route::put('/admin/{id}', [AdminController::class, 'update'])
            ->name('update')
            ->middleware('can:users-edit');
    Route::delete('/admin/{id}', [AdminController::class, 'delete'])
            ->name('delete')
            ->middleware('can:users-delete'); 

    Route::get('/member', [MemberController::class, 'index'])
            ->name('index')
            ->middleware('can:users-list');
    Route::post('/member', [MemberController::class, 'store'])
            ->name('store')
            ->middleware('can:users-create');
    Route::put('/member/{id}', [MemberController::class, 'update'])
            ->name('update')
            ->middleware('can:users-edit');
    Route::delete('/member/{id}', [MemberController::class, 'delete'])
            ->name('delete')
            ->middleware('can:users-delete'); 

    Route::get('/article', [ArticleController::class, 'index'])
            ->name('index');
    Route::post('/article', [ArticleController::class, 'store'])
            ->name('store');
    Route::put('/article/{id}', [ArticleController::class, 'update'])
            ->name('update');
    Route::delete('/article/{id}', [ArticleController::class, 'delete'])
            ->name('delete');
});
