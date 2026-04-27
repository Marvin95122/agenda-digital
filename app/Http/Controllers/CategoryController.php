<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category; // Esta es la línea que debe quedar exactamente así
use Illuminate\Support\Facades\Auth;

class CategoryController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        if (Auth::user()->role !== 'supervisor') abort(403);
        $categories = Category::where('user_id', Auth::id())->get();
        return view('categories.index', compact('categories'));
    }

    public function store(Request $request)
    {
        if (Auth::user()->role !== 'supervisor') abort(403);

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'color' => ['required', 'string', 'max:7'],
        ]);

        Category::create([
            'user_id' => Auth::id(),
            'name' => $request->name,
            'color' => $request->color,
        ]);

        return back()->with('success', 'Categoría creada correctamente.');
    }

    public function update(Request $request, Category $category)
    {
        if (Auth::user()->role !== 'supervisor') abort(403);

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'color' => ['required', 'string', 'max:7'],
        ]);

        $category->update([
            'name' => $request->name,
            'color' => $request->color,
        ]);

        return back()->with('success', 'Categoría actualizada.');
    }

    public function destroy(Category $category)
    {
        if (Auth::user()->role !== 'supervisor') abort(403);
        $category->delete();
        return back()->with('success', 'Categoría eliminada.');
    }
}