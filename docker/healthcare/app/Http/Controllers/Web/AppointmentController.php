<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AppointmentController extends Controller
{
    public function index() { return response()->json(['resource' => 'appointments']); }
    public function create() { return response()->json(['resource' => 'appointments.create']); }
    public function store(Request $request) { return response()->json(['resource' => 'appointments.store']); }
    public function show($id) { return response()->json(['resource' => 'appointments.show', 'id' => $id]); }
    public function edit($id) { return response()->json(['resource' => 'appointments.edit', 'id' => $id]); }
    public function update(Request $request, $id) { return response()->json(['resource' => 'appointments.update', 'id' => $id]); }
    public function destroy($id) { return response()->json(['resource' => 'appointments.destroy', 'id' => $id]); }
}
