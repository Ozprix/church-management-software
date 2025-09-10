<?php

namespace App\Http\Controllers\Group;

use App\Http\Controllers\Controller;
use App\Repositories\Interfaces\EventCategoryRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class EventCategoryController extends Controller
{
    /**
     * @var EventCategoryRepositoryInterface
     */
    protected $eventCategoryRepository;

    /**
     * EventCategoryController constructor.
     *
     * @param EventCategoryRepositoryInterface $eventCategoryRepository
     */
    public function __construct(EventCategoryRepositoryInterface $eventCategoryRepository)
    {
        $this->eventCategoryRepository = $eventCategoryRepository;
    }

    /**
     * Display a listing of the event categories.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $categories = $this->eventCategoryRepository->getAllCategories();

        return response()->json([
            'status' => 'success',
            'data' => $categories
        ]);
    }

    /**
     * Display a listing of active event categories.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getActiveCategories()
    {
        $categories = $this->eventCategoryRepository->getActiveCategories();

        return response()->json([
            'status' => 'success',
            'data' => $categories
        ]);
    }

    /**
     * Store a newly created event category in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'color' => 'nullable|string|max:7',
            'description' => 'nullable|string',
            'is_active' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $category = $this->eventCategoryRepository->createCategory($request->all());

            return response()->json([
                'status' => 'success',
                'message' => 'Event category created successfully',
                'data' => $category
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create event category',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified event category.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $category = $this->eventCategoryRepository->getCategoryById($id);

        if (!$category) {
            return response()->json([
                'status' => 'error',
                'message' => 'Event category not found'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $category
        ]);
    }

    /**
     * Update the specified event category in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'color' => 'nullable|string|max:7',
            'description' => 'nullable|string',
            'is_active' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $category = $this->eventCategoryRepository->updateCategory($id, $request->all());

            if (!$category) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Event category not found'
                ], 404);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Event category updated successfully',
                'data' => $category
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update event category',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified event category from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        try {
            $result = $this->eventCategoryRepository->deleteCategory($id);

            if (!$result) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Event category not found'
                ], 404);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Event category deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete event category',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
