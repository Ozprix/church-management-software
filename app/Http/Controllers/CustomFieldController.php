<?php

namespace App\Http\Controllers;

use App\Models\CustomFieldSchema;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class CustomFieldController extends Controller
{
    /**
     * Display a listing of the custom fields for a specific entity type.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $entityType = $request->query('entity_type');
        
        $query = CustomFieldSchema::query();
        
        if ($entityType) {
            $query->where('entity_type', $entityType);
        }
        
        $customFields = $query->orderBy('order')->get();
        
        return response()->json([
            'status' => 'success',
            'data' => $customFields,
            'field_types' => CustomFieldSchema::getFieldTypes()
        ]);
    }

    /**
     * Store a newly created custom field schema in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'entity_type' => 'required|string|in:member,family,event,donation',
            'label' => 'required|string|max:255',
            'type' => 'required|string|in:text,textarea,number,date,select,multiselect,checkbox,radio,email,phone,url,file',
            'options' => 'nullable|array|required_if:type,select,multiselect,radio,checkbox',
            'required' => 'boolean',
            'validation' => 'nullable|string',
            'placeholder' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'order' => 'nullable|integer',
            'active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Generate a machine-friendly name from the label
        $name = Str::snake($request->label);
        
        // Check if name already exists for this entity type
        $existingField = CustomFieldSchema::where('entity_type', $request->entity_type)
            ->where('name', $name)
            ->first();
            
        if ($existingField) {
            $name = $name . '_' . time(); // Append timestamp to make it unique
        }
        
        $customField = new CustomFieldSchema($request->all());
        $customField->name = $name;
        $customField->created_by = Auth::id();
        $customField->save();
        
        return response()->json([
            'status' => 'success',
            'message' => 'Custom field created successfully',
            'data' => $customField
        ], 201);
    }

    /**
     * Display the specified custom field schema.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $customField = CustomFieldSchema::findOrFail($id);
        
        return response()->json([
            'status' => 'success',
            'data' => $customField
        ]);
    }

    /**
     * Update the specified custom field schema in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'label' => 'sometimes|required|string|max:255',
            'type' => 'sometimes|required|string|in:text,textarea,number,date,select,multiselect,checkbox,radio,email,phone,url,file',
            'options' => 'nullable|array|required_if:type,select,multiselect,radio,checkbox',
            'required' => 'boolean',
            'validation' => 'nullable|string',
            'placeholder' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'order' => 'nullable|integer',
            'active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $customField = CustomFieldSchema::findOrFail($id);
        
        // Don't allow changing entity_type or name after creation
        $customField->fill($request->except(['entity_type', 'name']));
        $customField->save();
        
        return response()->json([
            'status' => 'success',
            'message' => 'Custom field updated successfully',
            'data' => $customField
        ]);
    }

    /**
     * Remove the specified custom field schema from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $customField = CustomFieldSchema::findOrFail($id);
        $customField->delete();
        
        return response()->json([
            'status' => 'success',
            'message' => 'Custom field deleted successfully'
        ]);
    }

    /**
     * Reorder custom fields.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function reorder(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'entity_type' => 'required|string|in:member,family,event,donation',
            'field_order' => 'required|array',
            'field_order.*' => 'required|integer|exists:custom_field_schemas,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $entityType = $request->entity_type;
        $fieldOrder = $request->field_order;
        
        // Update the order of each field
        foreach ($fieldOrder as $index => $fieldId) {
            CustomFieldSchema::where('id', $fieldId)
                ->where('entity_type', $entityType)
                ->update(['order' => $index]);
        }
        
        return response()->json([
            'status' => 'success',
            'message' => 'Custom fields reordered successfully'
        ]);
    }
}
