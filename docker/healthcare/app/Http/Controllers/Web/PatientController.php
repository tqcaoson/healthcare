<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PatientController extends Controller
{
    public function index() { return response()->json(['resource' => 'patients']); }
    public function create() { return response()->json(['resource' => 'patients.create']); }
    public function store(Request $request) { return response()->json(['resource' => 'patients.store']); }
    public function show($id) { return response()->json(['resource' => 'patients.show', 'id' => $id]); }
    public function edit($id) { return response()->json(['resource' => 'patients.edit', 'id' => $id]); }
    public function update(Request $request, $id) { return response()->json(['resource' => 'patients.update', 'id' => $id]); }
    public function destroy($id) { return response()->json(['resource' => 'patients.destroy', 'id' => $id]); }
}
