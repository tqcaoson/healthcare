<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class EncounterController extends Controller
{
    public function index() { return response()->json(['resource' => 'encounters']); }
    public function create() { return response()->json(['resource' => 'encounters.create']); }
    public function store(Request $request) { return response()->json(['resource' => 'encounters.store']); }
    public function show($id) { return response()->json(['resource' => 'encounters.show', 'id' => $id]); }
    public function edit($id) { return response()->json(['resource' => 'encounters.edit', 'id' => $id]); }
    public function update(Request $request, $id) { return response()->json(['resource' => 'encounters.update', 'id' => $id]); }
    public function destroy($id) { return response()->json(['resource' => 'encounters.destroy', 'id' => $id]); }
}
