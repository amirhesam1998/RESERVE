<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Categories\CreateCategory;
use App\Http\Requests\categories\UpdateCategory;
use App\Models\Category;
use Illuminate\Http\Request;
use Throwable;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorize('categories', ['view-categories']);

        try {
            $categories = Category::whereNull('parent_id')->latest()->get();

            return view('categories.index', compact('categories'));
        } catch (Throwable $e) {
            return back()->with('error', 'Somthing went wrong, Try again later');
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->authorize('categories', ['create-category']);

        try {
            $rootCategories = Category::whereNull('parent_id')->with('children')->latest()->get();

            $categories = [];
            foreach ($rootCategories as $root) {
                $categories = array_merge($categories, $root->getFlatTree());
            }

            return view('categories.create', compact('categories'));
        } catch (Throwable $e) {
            return back()->with('error', 'Somthing went wrong, Try again later');
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CreateCategory $request)
    {

        $this->authorize('categories', ['create-category']);

        try {
            $category = Category::create(
                $request->validated()
            );

            return redirect()->route('category.index');
        } catch (Throwable $e) {
            return back()->withInput()->with('error', 'Somthing went wrong, Try again later');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Category $category)
    {
        $this->authorize('categories', ['view-categories']);

        try {
            return view('categories.single', compact('category'));
        } catch (Throwable $e) {
            return back()->with('error', 'Somthing went wrong, Try again later');
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Category $category)
    {
        $this->authorize('categories', ['edit-categories']);

        try {
            $excluded = array_merge(
                [$category->id],
                $category->decentialids()
            );

            $rootCategories = Category::whereNull('parent_id')->whereNotIn('id', $excluded)->with(['childrenRecursive' => function ($query) use ($excluded) {
                $query->whereNotIn('id', $excluded);
            }])->get();

            $parent = Category::find($category->parent_id);

            return view('categories.edit', compact('categories', 'category', 'parent'));
        } catch (Throwable $e) {
            return back()->with('error', 'Somthing went wrong, Try again later');
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCategory $request, Category $category)
    {
        $this->authorize('categories', ['edit-categories']);

        try {
            $category->update($request->validated());

            return redirect()->route('category.index', $category->id);
        } catch (Throwable $e) {
            return back()->withInput()->with('error', 'Somthing went wrong, Try again later');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Category $category)
    {
        $this->authorize('categories', ['delete-categories']);

        try {
            $category->delete();

            return redirect()->route('category.index');
        } catch (Throwable $e) {
            return back()->with('error', 'Somthing went wrong, Try again later');
        }
    }
}
