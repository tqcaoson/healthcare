<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AppointmentController extends Controller
{
    public function index()
    {
        return view('appointments.index');
    }

    public function create()
    {
        return view('appointments.create');
    }

    public function store(Request $request)
    {
        return redirect()->route('appointments.index');
    }

    public function show($id)
    {
        return view('appointments.show', ['id' => $id]);
    }

    public function edit($id)
    {
        return view('appointments.edit', ['id' => $id]);
    }

    public function update(Request $request, $id)
    {
        return redirect()->route('appointments.show', $id);
    }

    public function destroy($id)
    {
        return redirect()->route('appointments.index');
    }
}
