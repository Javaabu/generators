<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Javaabu\Helpers\Traits\HasOrderbys;
use App\Http\Controllers\Controller;
use App\Http\Requests\CategoriesRequest;
use App\Models\Category;
use App\Exports\CategoriesExport;

class CategoriesController extends Controller
{
    use HasOrderbys;

    /**
     * Create a new  controller instance.
     */
    public function __construct()
    {
        $this->authorizeResource(Category::class);
    }

    /**
     * Initialize orderbys
     */
    protected static function initOrderbys()
    {
        static::$orderbys = [
            'id' => __('Id'),
            'created_at' => __('Created At'),
            'updated_at' => __('Updated At'),
            'name' => __('Name'),
            'slug' => __('Slug'),
        ];
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $title = __('All Categories');
        $orderby = $this->getOrderBy($request, 'created_at');
        $order = $this->getOrder($request, 'created_at', $orderby);
        $per_page = $this->getPerPage($request);

        $categories = Category::orderBy($orderby, $order);

        $search = null;
        if ($search = $request->input('search')) {
            $categories->search($search);
            $title = __('Categories matching \':search\'', ['search' => $search]);
        }

        if ($date_field = $request->input('date_field')) {
            $categories->dateBetween($date_field, $request->input('date_from'), $request->input('date_to'));
        }

        if ($request->download) {
            return (new CategoriesExport($categories))->download('categories.xlsx');
        }

        $categories = $categories->paginate($per_page)
                                 ->appends($request->except('page'));

        return view('admin.categories.index', compact('categories', 'title', 'per_page', 'search'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        return view('admin.categories.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CategoriesRequest $request)
    {
        $category = new Category($request->validated());

        $category->save();

        $this->flashSuccessMessage();

        return redirect()->action([CategoriesController::class, 'edit'], $category);
    }

    /**
     * Display the specified resource.
     */
    public function show(Category $category)
    {
        return view('admin.categories.show', compact('category'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Category $category)
    {
        return view('admin.categories.edit', compact('category'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(CategoriesRequest $request, Category $category)
    {
        $category->fill($request->validated());

        $category->save();

        $this->flashSuccessMessage();

        return redirect()->action([CategoriesController::class, 'edit'], $category);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Category $category, Request $request)
    {
        $category->delete();

        if ($request->expectsJson()) {
            return response()->json(true);
        }

        return redirect()->action([CategoriesController::class, 'index']);
    }

    /**
     * Perform bulk action on the resource
     */
    public function bulk(Request $request)
    {
        $this->authorize('viewAny', Category::class);

        $this->validate($request, [
            'action' => 'required|in:delete',
            'categories' => 'required|array',
            'categories.*' => 'exists:categories,id',
        ]);

        $action = $request->input('action');
        $ids = $request->input('categories', []);

        switch ($action) {
            case 'delete':
                // make sure allowed to delete
                $this->authorize('delete_categories');

                Category::whereIn('id', $ids)
                    ->get()
                    ->each(function (Category $category) {
                        $category->delete();
                    });
                break;
        }

        $this->flashSuccessMessage();

        return $this->redirect($request, action([CategoriesController::class, 'index']));
    }
}
