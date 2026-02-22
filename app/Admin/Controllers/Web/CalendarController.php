<?php

namespace App\Admin\Controllers\Web;

use App\Core\Services\CalendarService;
use App\Http\Controllers\Controller;
use App\Models\CalendarEvent;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CalendarController extends Controller
{
    public function __construct(
        protected CalendarService $calendarService
    ) {}

    public function index(): View
    {
        return view('admin.calendar.index');
    }

    public function getEvents(Request $request): JsonResponse
    {
        $start = $request->input('start');
        $end = $request->input('end');

        if ($start && $end) {
            $start = str_replace(' ', '+', $start);
            $end = str_replace(' ', '+', $end);
            $startDate = Carbon::parse($start);
            $endDate = Carbon::parse($end);
            $events = $this->calendarService->getEventsBetweenDates($startDate, $endDate);
        } else {
            $year = $request->input('year', now()->year);
            $month = $request->input('month', now()->month);
            $events = $this->calendarService->getEventsForMonth($year, $month);
        }

        return response()->json($events);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'event_type' => 'required|in:document,payment,maintenance,leave,delivery,meeting,inspection,other',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'start_time' => 'nullable|date_format:H:i',
            'end_time' => 'nullable|date_format:H:i|after:start_time',
            'is_all_day' => 'boolean',
            'priority' => 'required|in:low,medium,high',
            'color' => 'nullable|string|max:20',
        ]);

        $event = $this->calendarService->createEvent($validated);

        return response()->json($event, 201);
    }

    public function show(CalendarEvent $event): JsonResponse
    {
        $event->load(['related', 'creator']);

        return response()->json($event);
    }

    public function update(Request $request, CalendarEvent $event): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'event_type' => 'sometimes|in:document,payment,maintenance,leave,delivery,meeting,inspection,other',
            'start_date' => 'sometimes|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'start_time' => 'nullable|date_format:H:i',
            'end_time' => 'nullable|date_format:H:i',
            'is_all_day' => 'boolean',
            'priority' => 'sometimes|in:low,medium,high',
            'status' => 'sometimes|in:pending,completed,overdue,cancelled',
            'color' => 'nullable|string|max:20',
        ]);

        $event = $this->calendarService->updateEvent($event, $validated);

        return response()->json($event);
    }

    public function destroy(CalendarEvent $event): JsonResponse
    {
        $this->calendarService->deleteEvent($event);

        return response()->json(['message' => 'Event deleted successfully']);
    }
}
