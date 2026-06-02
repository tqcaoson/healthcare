<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class EncounterController extends Controller
{
    public function index()
    {
        return view('encounters.index');
    }

    public function create()
    {
        return view('encounters.create');
    }

    public function store(Request $request)
    {
        return redirect()->route('encounters.index');
    }

    public function show($id)
    {
        return view('encounters.show', ['id' => $id]);
    }

    public function edit($id)
    {
        return view('encounters.edit', ['id' => $id]);
    }

    public function update(Request $request, $id)
    {
        return redirect()->route('encounters.show', $id);
    }

    public function destroy($id)
    {
        return redirect()->route('encounters.index');
    }
}
