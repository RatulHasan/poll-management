<?php

use App\Http\Controllers\Admin\PollController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PublicPollController;
use App\Services\PollService;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get( '/', function () {
    $pollService = app( PollService::class );

    return Inertia::render( 'Welcome', [
        'polls'          => $pollService->getActivePolls(),
        'canLogin'       => Route::has( 'login' ),
        'canRegister'    => Route::has( 'register' ),
        'laravelVersion' => Application::VERSION,
        'phpVersion'     => PHP_VERSION,
    ] );
} );

Route::get( '/dashboard', [ App\Http\Controllers\DashboardController::class, 'index' ] )
     ->middleware( [ 'auth', 'verified' ] )
     ->name( 'dashboard' );

Route::middleware( 'auth' )->group( function () {
    Route::get( '/profile', [ ProfileController::class, 'edit' ] )->name( 'profile.edit' );
    Route::patch( '/profile', [ ProfileController::class, 'update' ] )->name( 'profile.update' );
    Route::delete( '/profile', [ ProfileController::class, 'destroy' ] )->name( 'profile.destroy' );

    // Admin Poll Management Routes
    Route::prefix( 'admin' )->name( 'admin.' )->group( function () {
        Route::resource( 'polls', PollController::class );
        Route::patch( 'polls/{poll}/toggle-status', [ PollController::class, 'toggleStatus' ] )
             ->name( 'polls.toggle-status' );
    } );
} );

// Public Poll Routes
Route::get( '/polls', [ PublicPollController::class, 'index' ] )->name( 'polls.index' );
Route::get( '/polls/{poll}', [ PublicPollController::class, 'show' ] )->name( 'polls.show' );
Route::get( '/polls/{poll}/vote', [ PublicPollController::class, 'show' ] )->name( 'polls.vote.form' );
Route::post( '/polls/{poll}/vote', [ PublicPollController::class, 'vote' ] )->name( 'polls.vote' );

require __DIR__ . '/auth.php';


// Handle wildcard routes without showing 404
Route::get( '/{any}', function () {
    $pollService = app( PollService::class );

    return Inertia::render( 'Welcome', [
        'polls'          => $pollService->getActivePolls(),
        'canLogin'       => Route::has( 'login' ),
        'canRegister'    => Route::has( 'register' ),
        'laravelVersion' => Application::VERSION,
        'phpVersion'     => PHP_VERSION,
    ] );
} )->where( 'any', '.*' );
