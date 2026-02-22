<?php

namespace App\Employee\Controllers\Web;

use App\Enums\AttendanceStatus;
use App\Http\Controllers\Controller;
use App\Models\Personel;
use App\Models\PersonnelAttendance;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;
use Throwable;

class PersonnelAttendanceController extends Controller
{
    /**
     * Puantaj tablosu sayfası.
     */
    public function index(Request $request): View
    {
        $month = $request->get('month', now()->format('Y-m'));
        $date = Carbon::parse($month.'-01');
        $daysInMonth = $date->daysInMonth;

        $personnelData = $this->buildPersonnelData($month, $daysInMonth);
        $attendanceStatuses = AttendanceStatus::forFrontend();
        $nextStatus = AttendanceStatus::cycleOrderMap();

        $safeJsonFlags = JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE;
        $config = [
            'month' => $month,
            'daysInMonth' => $daysInMonth,
            'personnelData' => $personnelData,
            'attendanceStatuses' => $attendanceStatuses,
            'nextStatus' => $nextStatus,
            'storeUrl' => route('admin.personnel_attendance.store'),
            'apiTableUrl' => route('admin.personnel_attendance.api.table'),
            'csrfToken' => csrf_token(),
        ];

        return view('admin.personnel-attendance.index', [
            'month' => $month,
            'daysInMonth' => $daysInMonth,
            'personnelData' => $personnelData,
            'attendanceStatuses' => $attendanceStatuses,
            'nextStatus' => $nextStatus,
            'statusList' => $attendanceStatuses,
            'nextStatusMap' => $nextStatus,
            'configJson' => json_encode($config, $safeJsonFlags),
        ]);
    }

    /**
     * Tek gün durumu kaydet (AJAX).
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'personnel_id' => 'required|integer|exists:personels,id',
                'date' => 'required|date',
                'status' => 'required|string|in:full,half,izin,yillik,rapor,none',
            ]);

            $personelId = (int) $validated['personnel_id'];
            $date = $validated['date'];
            $status = AttendanceStatus::fromFrontendKey($validated['status']);

            if (! $status) {
                return response()->json(['success' => false, 'message' => 'Geçersiz durum.']);
            }

            if (! Schema::hasColumn((new PersonnelAttendance)->getTable(), 'personel_id')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Puantaj tablosu güncel değil. Lütfen "php artisan migrate" çalıştırın.',
                ], 503);
            }

            $record = PersonnelAttendance::query()
                ->withTrashed()
                ->where('personel_id', $personelId)
                ->whereDate('attendance_date', $date)
                ->first();

            if ($status === AttendanceStatus::Absent) {
                if ($record) {
                    $record->delete();
                }

                return response()->json(['success' => true]);
            }

            if ($record) {
                if ($record->trashed()) {
                    $record->restore();
                }
                $record->attendance_type = $status;
                $record->save();
            } else {
                PersonnelAttendance::create([
                    'personel_id' => $personelId,
                    'employee_id' => null,
                    'attendance_date' => $date,
                    'attendance_type' => $status,
                ]);
            }

            return response()->json(['success' => true]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Kaydetme hatası: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Tablo verisi (AJAX, ay değişince).
     */
    public function apiTable(Request $request): JsonResponse
    {
        $month = $request->get('month', now()->format('Y-m'));
        $date = Carbon::parse($month.'-01');
        $daysInMonth = $date->daysInMonth;

        $personnelData = $this->buildPersonnelData($month, $daysInMonth);

        return response()->json([
            'success' => true,
            'personnelData' => $personnelData,
            'daysInMonth' => $daysInMonth,
        ]);
    }

    /**
     * @return array<int, array{id: int, name: string, attendance: array<int, string>}>
     */
    private function buildPersonnelData(string $month, int $daysInMonth): array
    {
        $personels = Personel::query()
            ->where('aktif', true)
            ->orderBy('ad_soyad')
            ->get();

        $attendances = collect();
        if (Schema::hasColumn((new PersonnelAttendance)->getTable(), 'personel_id')) {
            $start = Carbon::parse($month.'-01');
            $end = $start->copy()->endOfMonth();
            $attendances = PersonnelAttendance::query()
                ->whereIn('personel_id', $personels->pluck('id'))
                ->whereBetween('attendance_date', [$start, $end])
                ->get()
                ->groupBy('personel_id');
        }

        $personnelData = [];
        foreach ($personels as $personel) {
            $attendanceMap = [];
            for ($d = 1; $d <= $daysInMonth; $d++) {
                $attendanceMap[$d] = 'none';
            }
            foreach ($attendances->get($personel->id, []) as $a) {
                $day = (int) $a->attendance_date->format('d');
                $attendanceMap[$day] = $a->getFrontendStatusKey();
            }
            $personnelData[] = [
                'id' => $personel->id,
                'name' => $personel->ad_soyad ?? '',
                'attendance' => $attendanceMap,
            ];
        }

        return $personnelData;
    }
}
