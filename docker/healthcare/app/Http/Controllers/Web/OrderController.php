<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index()
    {
        return view('orders.index');
    }

    public function create()
    {
        return view('orders.place-order');
    }

    public function store(Request $request)
    {
        return redirect()->route('orders.index');
    }

    public function show($id)
    {
        return view('orders.show', ['id' => $id]);
    }

    public function edit($id)
    {
        return view('orders.edit', ['id' => $id]);
    }

    public function update(Request $request, $id)
    {
        return redirect()->route('orders.show', $id);
    }

    public function destroy($id)
    {
        return redirect()->route('orders.index');
    }
}
