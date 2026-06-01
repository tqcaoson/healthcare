<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ResultController extends Controller
{
    public function index() { return response()->json(['resource' => 'results']); }
    public function create() { return response()->json(['resource' => 'results.create']); }
    public function store(Request $request) { return response()->json(['resource' => 'results.store']); }
    public function show($id) { return response()->json(['resource' => 'results.show', 'id' => $id]); }
    public function edit($id) { return response()->json(['resource' => 'results.edit', 'id' => $id]); }
    public function update(Request $request, $id) { return response()->json(['resource' => 'results.update', 'id' => $id]); }
    public function destroy($id) { return response()->json(['resource' => 'results.destroy', 'id' => $id]); }
}
