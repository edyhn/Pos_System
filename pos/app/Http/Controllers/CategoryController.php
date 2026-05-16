<?php

namespace App\Http\Controllers;

class CategoryController extends Controller
{
    public function index()
    {
        return view('categories.index');
    }

    public function create()
    {
        return view('categories.create');
    }

    public function edit(\App\Models\Category $category)
    {
        return view('categories.edit', compact('category'));
    }
}
