<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;

class EventsController extends Controller
{
    public function index(Request $request)
    {
        $query = Event::query();
        if ($request->start_date) {
            $query->whereDate('start_time', '>=', $request->start_date);
        }
        if ($request->end_date) {
            $query->whereDate('end_time', '<=', $request->end_date);
        }
        $events = $query->latest('start_time')->paginate(20);
        return view('events', compact('events'));
    }

    public function create()
    {
        return view('events_create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after_or_equal:start_time',
            'location' => 'nullable|string',
        ]);
        Event::create($request->only('name', 'start_time', 'end_time', 'location'));
        return redirect()->route('events.index')->with('success', 'Event added successfully.');
    }

    public function edit(Event $event)
    {
        return view('events_edit', compact('event'));
    }

    public function update(Request $request, Event $event)
    {
        $request->validate([
            'name' => 'required',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after_or_equal:start_time',
            'location' => 'nullable|string',
        ]);
        $event->update($request->only('name', 'start_time', 'end_time', 'location'));
        return redirect()->route('events.index')->with('success', 'Event updated successfully.');
    }

    public function destroy(Event $event)
    {
        $event->delete();
        return redirect()->route('events.index')->with('success', 'Event deleted successfully.');
    }
}
