<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index() { return response()->json(['resource' => 'orders']); }
    public function create() { return response()->json(['resource' => 'orders.create']); }
    public function store(Request $request) { return response()->json(['resource' => 'orders.store']); }
    public function show($id) { return response()->json(['resource' => 'orders.show', 'id' => $id]); }
    public function edit($id) { return response()->json(['resource' => 'orders.edit', 'id' => $id]); }
    public function update(Request $request, $id) { return response()->json(['resource' => 'orders.update', 'id' => $id]); }
    public function destroy($id) { return response()->json(['resource' => 'orders.destroy', 'id' => $id]); }
}
