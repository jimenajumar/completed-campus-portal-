<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    // Display active users
    public function index()
    {
        $users = User::latest()->get();

        return view('admin.users', compact('users'));
    }

    // Display archived users
    public function archive(Request $request)
    {
        abort_unless($request->user()->role === 'admin', 403);

        $archivedUsers = User::onlyTrashed()
            ->latest('deleted_at')
            ->get();

        return view('admin.user-archive', compact('archivedUsers'));
    }

    // Create a user
    public function store(Request $request)
    {
        abort_unless($request->user()->role === 'admin', 403);

        $name = preg_replace('/\s+/', ' ', trim($request->name ?? ''));

        $request->merge([
            'name' => mb_convert_case($name, MB_CASE_TITLE, 'UTF-8')
        ]);

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                'regex:/^[\pL\s.\'-]+$/u',
                Rule::unique('users', 'name'),
            ],
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'role' => [
                'required',
                Rule::in(['admin', 'club_advisor', 'student']),
            ],
            'student_id' => [
                'required_if:role,student',
                'nullable',
                'string',
                'unique:users,student_id',
],
        ]);

        User::create($validated);

        return redirect('/admin/users')
            ->with('success', 'User account created successfully.');
    }

    // Archive a user account
    public function destroy(Request $request, User $user)
    {
        abort_unless($request->user()->role === 'admin', 403);

        if ((int) $request->user()->id === (int) $user->id) {
            return redirect('/admin/users')
                ->with('error', 'You cannot remove your own account.');
        }

        $user->tokens()->delete();

        $user->delete();

        return redirect('/admin/users')
            ->with('success', 'User account archived successfully.');
    }

    // Restore an archived account
    public function restore(Request $request, $id)
    {
        abort_unless($request->user()->role === 'admin', 403);

        $user = User::withTrashed()->findOrFail($id);

        if (!$user->trashed()) {
            return redirect('/admin/users/archive')
                ->with('error', 'This account is not archived.');
        }

        $user->restore();

        return redirect('/admin/users/archive')
            ->with('success', 'User account restored successfully.');
    }

    // Permanently delete an archived account
    public function forceDelete(Request $request, $id)
    {
        abort_unless($request->user()->role === 'admin', 403);

        $user = User::withTrashed()->findOrFail($id);

        if (!$user->trashed()) {
            return redirect('/admin/users/archive')
                ->with('error', 'Only archived accounts can be permanently deleted.');
        }

        if ((int) $request->user()->id === (int) $user->id) {
            return redirect('/admin/users/archive')
                ->with('error', 'You cannot delete your own account.');
        }

        $user->forceDelete();

        return redirect('/admin/users/archive')
            ->with('success', 'User account permanently deleted.');
    }
}
