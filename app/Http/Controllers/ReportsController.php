<?php

namespace App\Http\Controllers;

use App\Models\Report;
use Illuminate\Http\Request;

class ReportsController extends Controller
{
    public function index(Request $request)
    {
        $query = Report::query();
        if ($request->type) {
            $query->where('type', $request->type);
        }
        if ($request->start_date) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }
        if ($request->end_date) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }
        $reports = $query->latest()->paginate(20);
        $types = Report::select('type')->distinct()->pluck('type');
        return view('reports', compact('reports', 'types'));
    }

    public function create()
    {
        return view('reports_create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required',
            'type' => 'required',
        ]);
        Report::create($request->only('title', 'type'));
        return redirect()->route('reports.index')->with('success', 'Report added successfully.');
    }

    public function edit(Report $report)
    {
        return view('reports_edit', compact('report'));
    }

    public function update(Request $request, Report $report)
    {
        $request->validate([
            'title' => 'required',
            'type' => 'required',
        ]);
        $report->update($request->only('title', 'type'));
        return redirect()->route('reports.index')->with('success', 'Report updated successfully.');
    }

    public function destroy(Report $report)
    {
        $report->delete();
        return redirect()->route('reports.index')->with('success', 'Report deleted successfully.');
    }
}
