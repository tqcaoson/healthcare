<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PatientController extends Controller
{
    public function index()
    {
        return view('patients.index');
    }

    public function create()
    {
        return view('patients.create');
    }

    public function store(Request $request)
    {
        return redirect()->route('patients.index');
    }

    public function show($id)
    {
        return view('patients.show', ['id' => $id]);
    }

    public function edit($id)
    {
        return view('patients.edit', ['id' => $id]);
    }

    public function update(Request $request, $id)
    {
        return redirect()->route('patients.show', $id);
    }

    public function destroy($id)
    {
        return redirect()->route('patients.index');
    }
}
