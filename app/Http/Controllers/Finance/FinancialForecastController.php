<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Repositories\Interfaces\FinancialForecastRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class FinancialForecastController extends Controller
{
    protected $forecastRepository;

    /**
     * Create a new controller instance.
     *
     * @param FinancialForecastRepositoryInterface $forecastRepository
     */
    public function __construct(FinancialForecastRepositoryInterface $forecastRepository)
    {
        $this->middleware('auth:api');
        $this->forecastRepository = $forecastRepository;
    }

    /**
     * Get all financial forecasts.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        // Check permission
        if (!Auth::user()->can('view_financial_forecasts')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $filters = $request->only(['status', 'period_type', 'created_by', 'search', 'date', 'start_date', 'end_date']);
        $perPage = $request->input('per_page', 15);

        $forecasts = $this->forecastRepository->getAllForecasts($filters, $perPage);

        return response()->json($forecasts);
    }

    /**
     * Get a specific forecast.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        // Check permission
        if (!Auth::user()->can('view_financial_forecasts')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $forecast = $this->forecastRepository->getForecastById($id);

        if (!$forecast) {
            return response()->json(['error' => 'Forecast not found'], 404);
        }

        return response()->json($forecast);
    }

    /**
     * Create a new forecast.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        // Check permission
        if (!Auth::user()->can('create_financial_forecasts')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'period_type' => 'required|string|in:monthly,quarterly,annual,custom',
            'status' => 'required|string|in:draft,active,archived',
            'items' => 'nullable|array',
            'items.*.name' => 'required|string|max:255',
            'items.*.description' => 'nullable|string',
            'items.*.type' => 'required|string|in:income,expense',
            'items.*.category' => 'required|string|max:255',
            'items.*.amount' => 'required|numeric|min:0',
            'items.*.expected_date' => 'nullable|date',
            'items.*.is_recurring' => 'nullable|boolean',
            'items.*.recurrence_pattern' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $request->all();
        $data['created_by'] = Auth::id();

        $forecast = $this->forecastRepository->createForecast($data);

        return response()->json($forecast, 201);
    }

    /**
     * Update a forecast.
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        // Check permission
        if (!Auth::user()->can('update_financial_forecasts')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $forecast = $this->forecastRepository->getForecastById($id);

        if (!$forecast) {
            return response()->json(['error' => 'Forecast not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'sometimes|required|date',
            'end_date' => 'sometimes|required|date|after_or_equal:start_date',
            'period_type' => 'sometimes|required|string|in:monthly,quarterly,annual,custom',
            'status' => 'sometimes|required|string|in:draft,active,archived',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $this->forecastRepository->updateForecast($id, $request->all());

        $updatedForecast = $this->forecastRepository->getForecastById($id);

        return response()->json($updatedForecast);
    }

    /**
     * Delete a forecast.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        // Check permission
        if (!Auth::user()->can('delete_financial_forecasts')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $forecast = $this->forecastRepository->getForecastById($id);

        if (!$forecast) {
            return response()->json(['error' => 'Forecast not found'], 404);
        }

        $this->forecastRepository->deleteForecast($id);

        return response()->json(['message' => 'Forecast deleted successfully']);
    }

    /**
     * Add an item to a forecast.
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function addItem(Request $request, $id)
    {
        // Check permission
        if (!Auth::user()->can('update_financial_forecasts')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $forecast = $this->forecastRepository->getForecastById($id);

        if (!$forecast) {
            return response()->json(['error' => 'Forecast not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|string|in:income,expense',
            'category' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'expected_date' => 'nullable|date',
            'is_recurring' => 'nullable|boolean',
            'recurrence_pattern' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $this->forecastRepository->addForecastItem($id, $request->all());

        $updatedForecast = $this->forecastRepository->getForecastById($id);

        return response()->json($updatedForecast);
    }

    /**
     * Update a forecast item.
     *
     * @param Request $request
     * @param int $forecastId
     * @param int $itemId
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateItem(Request $request, $forecastId, $itemId)
    {
        // Check permission
        if (!Auth::user()->can('update_financial_forecasts')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $forecast = $this->forecastRepository->getForecastById($forecastId);

        if (!$forecast) {
            return response()->json(['error' => 'Forecast not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'sometimes|required|string|in:income,expense',
            'category' => 'sometimes|required|string|max:255',
            'amount' => 'sometimes|required|numeric|min:0',
            'expected_date' => 'nullable|date',
            'is_recurring' => 'nullable|boolean',
            'recurrence_pattern' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $this->forecastRepository->updateForecastItem($itemId, $request->all());

        $updatedForecast = $this->forecastRepository->getForecastById($forecastId);

        return response()->json($updatedForecast);
    }

    /**
     * Delete a forecast item.
     *
     * @param int $forecastId
     * @param int $itemId
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteItem($forecastId, $itemId)
    {
        // Check permission
        if (!Auth::user()->can('update_financial_forecasts')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $forecast = $this->forecastRepository->getForecastById($forecastId);

        if (!$forecast) {
            return response()->json(['error' => 'Forecast not found'], 404);
        }

        $this->forecastRepository->deleteForecastItem($itemId);

        $updatedForecast = $this->forecastRepository->getForecastById($forecastId);

        return response()->json($updatedForecast);
    }

    /**
     * Get variance between forecast and actual for a specific date range.
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getVariance(Request $request, $id)
    {
        // Check permission
        if (!Auth::user()->can('view_financial_forecasts')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $forecast = $this->forecastRepository->getForecastById($id);

        if (!$forecast) {
            return response()->json(['error' => 'Forecast not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $variance = $this->forecastRepository->getVariance($id, $request->input('start_date'), $request->input('end_date'));

        return response()->json($variance);
    }
}
