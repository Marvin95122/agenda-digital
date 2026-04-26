<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class PersonnelController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        if (auth()->user()->role !== 'supervisor') abort(403);
        $personnel = User::where('role', 'enfermeria')->get();
        return view('personnel.index', compact('personnel'));
    }

    public function store(Request $request)
    {
        if (auth()->user()->role !== 'supervisor') abort(403);

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8'],
            'shift' => ['required', 'string'],
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'shift' => $request->shift,
            'role' => 'enfermeria', 
        ]);

        return back()->with('success', 'Personal registrado correctamente.');
    }

    public function update(Request $request, User $user)
    {
        if (auth()->user()->role !== 'supervisor') abort(403);

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'shift' => ['required', 'string'],
        ]);

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'shift' => $request->shift,
        ]);

        if ($request->filled('password')) {
            $user->update(['password' => Hash::make($request->password)]);
        }

        return back()->with('success', 'Datos del personal actualizados.');
    }

    public function destroy(User $user)
    {
        if (auth()->user()->role !== 'supervisor') abort(403);
        $user->delete();
        return back()->with('success', 'Personal eliminado del sistema.');
    }
}