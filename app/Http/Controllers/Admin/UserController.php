<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index()
    {
        $users = User::latest()->get();

        return view('admin.users', compact('users'));
    }

    public function store(Request $request)
{
    // Clean and format the name
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
        'student_id' => 'nullable|string|unique:users,student_id',
    ]);

    User::create($validated);

    return redirect('/admin/users')
        ->with('success', 'User account created successfully.');
}
}
