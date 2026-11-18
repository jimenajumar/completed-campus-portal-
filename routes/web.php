<?php
use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\SettingController;

Route::get('/', function () {
    return redirect('/login');
});

Route::get('/login', function () {
    if (Auth::check()) {
        $user = Auth::user();

        return redirect(match ($user->role) {
            'admin' => '/admin/dashboard',
            'club_advisor' => '/club-advisor/dashboard',
            default => '/dashboard',
        });
    }

    return view('login');
});

// STUDENT AND SHARED PORTAL PAGES
Route::middleware('auth')->group(function () {

    Route::get('/dashboard', function () {
        abort_unless(Auth::user()->role === 'student', 403);

        return view('dashboard');
    });

    Route::get('/schedule', function () {
        return view('schedule');
    });

    Route::get('/events', function () {
        return view('events');
    });

    Route::get('/announcements', function () {
        return view('announcements');
    });

    Route::get('/weather', function () {
        return view('weather');
    });

    Route::get('/profile', function () {
        return view('profile');
    });

    Route::get('/clubs', function () {
        return view('clubs');
    });

    Route::get('/rooms', function () {
        return view('rooms');
    });

    Route::get('/room-assignments', function () {
        return view('room-assignments');
    });

});

Route::middleware('auth')->group(function () {

    Route::get('/club-advisor/dashboard', function () {
        abort_unless(Auth::user()->role === 'club_advisor', 403);

        return view('clubAdvisor.advisorboard');
    });
    Route::get('/club-advisor/memberships', function () {
    abort_unless(Auth::user()->role === 'club_advisor', 403);
    return view('clubAdvisor.memberships');
    });
    Route::get('/club-advisor/clubs', function () {
    abort_unless(Auth::user()->role === 'club_advisor', 403);
    return view('clubAdvisor.advisorclub');
    });
    Route::get('/club-advisor/announcements', function () {
    abort_unless(Auth::user()->role === 'club_advisor', 403);
    return view('clubAdvisor.clubannouncement');
});
Route::get('/club-advisor/clubs/{club}/members', function ($club) {
    abort_unless(Auth::user()->role === 'club_advisor', 403);

    return view('clubAdvisor.members', [
        'clubId' => $club
    ]);
})->name('club-advisor.members');

});
Route::middleware(['auth', 'admin'])->group(function () {

    Route::get('/admin/dashboard', [AdminDashboardController::class, 'index']);
    Route::get('/admin/users', [UserController::class, 'index']);
    Route::post('/admin/users', [UserController::class, 'store']);
    Route::get('/admin/events', function () {
    return view('admin.adminevents');
});
// Admin Settings
Route::get('/admin/settings', [SettingController::class, 'index'])
    ->name('admin.settings');

Route::put('/admin/settings', [SettingController::class, 'update'])
    ->name('admin.settings.update');
Route::get('/admin/announcements', function () {
    return view('admin.adminannouncements');
});
Route::get('/admin/clubs', function () {
    return view('admin.adminclubs');
});
Route::get('/admin/clubs/archive', function () {
    return view('admin.club-archive');
})->name('admin.clubs.archive');
Route::get('/admin/rooms', function () {
    return view('admin.adminrooms');
});
Route::get('/admin/room-assignments', function () {
    return view('admin.adminroomasign');
});
Route::get('/admin/activity-logs', function () {
    return view('admin.adminactivitylogs');
});
Route::get('/admin/memberships', function () {
    return view('admin.adminclubmembership');
});

Route::delete('/admin/users/{user}', [
    \App\Http\Controllers\Admin\UserController::class,
    'destroy'
])->middleware('auth');

Route::get('/admin/users/archive', [UserController::class, 'archive'])
    ->name('admin.users.archive');

Route::patch('/admin/users/{id}/restore', [UserController::class, 'restore'])
    ->name('admin.users.restore');

Route::delete('/admin/users/{id}/force-delete', [UserController::class, 'forceDelete'])
    ->name('admin.users.forceDelete');

});

Route::post('/web-login', function (Request $request) {
    $credentials = $request->validate([
        'email' => 'required|email',
        'password' => 'required|string',
    ]);

    if (!Auth::attempt($credentials)) {
        return response()->json([
            'message' => 'Invalid email or password.',
        ], 401);
    }

    $request->session()->regenerate();

    $user = Auth::user();

    // Keep the API token for your existing dashboard requests.
    $token = $user->createToken('campus-portal')->plainTextToken;

    return response()->json([
        'message' => 'Login successful.',
        'user' => [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'student_id' => $user->student_id,
        ],
        'token' => $token,
        'redirect' => match ($user->role) {
    'admin' => '/admin/dashboard',
    'club_advisor' => '/club-advisor/dashboard',
    default => '/dashboard',
},
    ]);
});
Route::post('/web-logout', function (Request $request) {
    Auth::logout();

    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return redirect('/login');
})->middleware('auth');
