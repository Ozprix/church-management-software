<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\DonationCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class DonationCategoryController extends Controller
{
    /**
     * Display a listing of the donation categories.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = DonationCategory::query();
        
        // Filter by active status if provided
        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }
        
        // Filter by tax-deductible status if provided
        if ($request->has('is_tax_deductible')) {
            $query->where('is_tax_deductible', $request->boolean('is_tax_deductible'));
        }
        
        $categories = $query->orderBy('name')->get();
        
        return response()->json([
            'status' => 'success',
            'data' => $categories
        ]);
    }

    /**
     * Store a newly created donation category in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'is_tax_deductible' => 'boolean',
            'is_default' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Generate a code from the name if not provided
        $code = $request->code ?? Str::slug($request->name);
        
        // Check if code already exists
        if (DonationCategory::where('code', $code)->exists()) {
            return response()->json([
                'status' => 'error',
                'message' => 'A category with this code already exists',
                'errors' => ['code' => ['The code has already been taken.']]
            ], 422);
        }
        
        // If this is set as default, unset any existing default
        if ($request->boolean('is_default')) {
            DonationCategory::where('is_default', true)->update(['is_default' => false]);
        }
        
        $category = DonationCategory::create([
            'name' => $request->name,
            'code' => $code,
            'description' => $request->description,
            'is_active' => $request->boolean('is_active', true),
            'is_tax_deductible' => $request->boolean('is_tax_deductible', true),
            'is_default' => $request->boolean('is_default', false),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Donation category created successfully',
            'data' => $category
        ], 201);
    }

    /**
     * Display the specified donation category.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $category = DonationCategory::findOrFail($id);
        
        return response()->json([
            'status' => 'success',
            'data' => $category
        ]);
    }

    /**
     * Update the specified donation category in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'is_tax_deductible' => 'boolean',
            'is_default' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $category = DonationCategory::findOrFail($id);
        
        // If this is set as default, unset any existing default
        if ($request->has('is_default') && $request->boolean('is_default') && !$category->is_default) {
            DonationCategory::where('is_default', true)->update(['is_default' => false]);
        }
        
        $category->update($request->all());

        return response()->json([
            'status' => 'success',
            'message' => 'Donation category updated successfully',
            'data' => $category
        ]);
    }

    /**
     * Remove the specified donation category from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $category = DonationCategory::findOrFail($id);
        
        // Check if this category is being used by any donations
        if ($category->donations()->exists()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Cannot delete category because it is being used by donations'
            ], 422);
        }
        
        // If this is the default category, don't allow deletion
        if ($category->is_default) {
            return response()->json([
                'status' => 'error',
                'message' => 'Cannot delete the default category'
            ], 422);
        }
        
        $category->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Donation category deleted successfully'
        ]);
    }
    
    /**
     * Set a category as the default.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function setDefault($id)
    {
        $category = DonationCategory::findOrFail($id);
        
        // Unset any existing default
        DonationCategory::where('is_default', true)->update(['is_default' => false]);
        
        // Set this category as default
        $category->is_default = true;
        $category->save();
        
        return response()->json([
            'status' => 'success',
            'message' => 'Default donation category set successfully',
            'data' => $category
        ]);
    }
}
