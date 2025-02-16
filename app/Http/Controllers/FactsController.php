<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Fact;

class FactsController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'fact' => 'required|string|max:255',
        ]);

        Fact::create([
            'fact' => $request->fact,
        ]);

        return redirect()->route('admin.facts')->with('success', 'Fact added successfully!');
    }

    public function destroy($id)
    {
        $fact = Fact::findOrFail($id);
        $fact->delete();

        return redirect()->route('admin.facts')->with('success', 'Fact deleted successfully!');
    }
}
