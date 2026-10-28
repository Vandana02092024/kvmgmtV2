<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Category;
use App\Http\Requests;
use App\Http\Requests\CategoryRequest;


class CategoryController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //$categories = Category::all();
		$categories = Category::orderBy('rank')->get();
        return view('categories.index', compact('categories'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $categories = Category::orderBy('rank')->get();
        return view('categories.index', compact('categories'))->with('openAddCategoryModal', true);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(CategoryRequest $request)
    {
        Category::create($request->all());
        return redirect()->route('category.index')->with('message','Category has been added successfully!');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Category $category)
    {
        $categories = Category::orderBy('rank')->get();
        return view('categories.index', compact('categories'))->with('openEditCategoryId', $category->id);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    // public function update(Request $request, Category $category)
    // {
    //     $category->update($request->all());
    //     return redirect()->route('category.index')->with('message','Category has been updated successfully!');
    // }

    
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'category' => 'required|string|max:255',
            'rank' => 'nullable|integer',
        ]);

        $category = Category::findOrFail($id);

        $category->category = $validated['category'];
        $category->rank = $validated['rank'] ?? null;

        $category->save();

        return redirect()
            ->route('category.index')
            ->with('message', 'Category has been updated successfully!');
    }

	
	public function catArchive($id , Request $request)
    {
		$statusCode = $request->st;
		
		
		if($statusCode == 1) {
		Category::where('id', '=', $id)->update(['status'   => 0]);   
		return redirect()->route('category.index')->with('message','Archived successfully!');
			
		} else {
		Category::where('id', '=', $id)->update(['status'   => 1]);   
		return redirect()->route('category.index')->with('message','Unarchived successfully!');
		}
		
		
		
		
    }
	
	
    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Category $category)
    {
        $category->delete();
        return redirect()->route('category.index')->with('message','Category has been deleted successfully!');
    }
}
