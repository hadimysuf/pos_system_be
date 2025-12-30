<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use App\Http\Resources\MenuResource;
use Illuminate\Http\Request;

class MenuController extends Controller
{
    public function index()
    {
        return MenuResource::collection(
            Menu::orderBy('order')->get()
        );
    }

    public function store(Request $request)
    {
        $request->validate([
            'code'  => 'required|unique:menus,code',
            'name'  => 'required',
            'route' => 'required',
            'icon'  => 'nullable',
            'order' => 'integer'
        ]);

        $menu = Menu::create($request->all());

        return new MenuResource($menu);
    }

    public function show(Menu $menu)
    {
        return new MenuResource($menu);
    }

    public function update(Request $request, Menu $menu)
    {
        $menu->update($request->all());
        return new MenuResource($menu);
    }

    public function destroy(Menu $menu)
    {
        $menu->delete();
        return response()->json(['message' => 'Menu deleted']);
    }
}
