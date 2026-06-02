<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ResultController extends Controller
{
    public function index()
    {
        return view('results.index');
    }

    public function create()
    {
        return view('results.create');
    }

    public function store(Request $request)
    {
        return redirect()->route('results.index');
    }

    public function show($id)
    {
        return view('results.show', ['id' => $id]);
    }

    public function edit($id)
    {
        return view('results.edit', ['id' => $id]);
    }

    public function update(Request $request, $id)
    {
        return redirect()->route('results.show', $id);
    }

    public function destroy($id)
    {
        return redirect()->route('results.index');
    }
}
