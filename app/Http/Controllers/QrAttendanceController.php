<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\QrAttendanceScanRequest;
use App\Models\Attendance;
use App\Models\ClassSection;
use App\Models\QrAttendanceLog;
use App\Models\Students;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class QrAttendanceController extends Controller
{
    /**
     * Show the QR scanner interface for the attendee teacher.
     */
    public function index(): View|RedirectResponse
    {
        if (! Auth::user()->can('qr-attendance-scan')) {
            return redirect(route('home'))->withErrors(['message' => trans('no_permission_message')]);
        }

        $classSections = ClassSection::with('class', 'section', 'class.medium', 'class.streams')->get();

        return view('qr_attendance.index', compact('classSections'));
    }

    /**
     * Process a scanned QR code and record attendance.
     */
    public function scan(QrAttendanceScanRequest $request): JsonResponse
    {
        try {
            $payload = $this->decodeQrPayload($request->qr_payload);

            if ($payload === null) {
                return response()->json(['error' => true, 'message' => 'Invalid QR code format.'], 422);
            }

            $student = Students::with('user', 'class_section')
                ->where('id', $payload['s'])
                ->where('qr_token', $payload['t'])
                ->first();

            if ($student === null) {
                return response()->json(['error' => true, 'message' => 'Invalid or unrecognized QR code.'], 422);
            }

            $sessionYear = getSettings('session_year');
            $sessionYearId = (int) $sessionYear['session_year'];
            $classSectionId = (int) $request->class_section_id;
            $date = now()->toDateString();

            // Prevent duplicate attendance for the same student on the same day in the same class section
            $alreadyMarked = Attendance::where([
                'student_id' => $student->id,
                'class_section_id' => $classSectionId,
                'date' => $date,
                'session_year_id' => $sessionYearId,
            ])->exists();

            if ($alreadyMarked) {
                return response()->json([
                    'error' => false,
                    'warning' => true,
                    'message' => 'Attendance already recorded for '.$student->user->full_name.' today.',
                    'student' => $this->buildStudentPayload($student),
                ]);
            }

            DB::transaction(function () use ($student, $classSectionId, $sessionYearId, $date, $request) {
                Attendance::create([
                    'student_id' => $student->id,
                    'class_section_id' => $classSectionId,
                    'session_year_id' => $sessionYearId,
                    'type' => 1, // 1 = Present
                    'date' => $date,
                    'remark' => 'QR scan',
                ]);

                QrAttendanceLog::create([
                    'student_id' => $student->id,
                    'scanned_by' => Auth::id(),
                    'class_section_id' => $classSectionId,
                    'session_year_id' => $sessionYearId,
                    'attendance_date' => $date,
                    'status' => 'present',
                    'ip_address' => $request->ip(),
                ]);
            });

            return response()->json([
                'error' => false,
                'message' => 'Attendance marked present for '.$student->user->full_name.'.',
                'student' => $this->buildStudentPayload($student),
            ]);
        } catch (\Throwable $th) {
            Log::error('QR attendance scan failed', ['error' => $th->getMessage()]);

            return response()->json(['error' => true, 'message' => 'An error occurred while processing the QR code.'], 500);
        }
    }

    /**
     * Show today's QR scan history for the authenticated user.
     */
    public function history(Request $request): View|RedirectResponse
    {
        if (! Auth::user()->can('qr-attendance-scan')) {
            return redirect(route('home'))->withErrors(['message' => trans('no_permission_message')]);
        }

        $date = $request->date ?? now()->toDateString();

        $logs = QrAttendanceLog::with('student', 'classSection')
            ->where('scanned_by', Auth::id())
            ->whereDate('attendance_date', $date)
            ->latest()
            ->get();

        return view('qr_attendance.history', compact('logs', 'date'));
    }

    /**
     * Decode and validate the QR payload (base64-encoded JSON).
     *
     * @return array{s: int, t: string}|null
     */
    private function decodeQrPayload(string $raw): ?array
    {
        $decoded = base64_decode($raw, strict: true);

        if ($decoded === false) {
            return null;
        }

        $payload = json_decode($decoded, associative: true);

        if (! is_array($payload) || ! isset($payload['s'], $payload['t'])) {
            return null;
        }

        if (! is_numeric($payload['s']) || ! is_string($payload['t']) || strlen($payload['t']) !== 64) {
            return null;
        }

        return ['s' => (int) $payload['s'], 't' => $payload['t']];
    }

    /**
     * Build a student data array for the JSON response.
     *
     * @return array<string, mixed>
     */
    private function buildStudentPayload(Students $student): array
    {
        return [
            'id' => $student->id,
            'name' => $student->user->full_name,
            'admission_no' => $student->admission_no,
            'roll_number' => $student->roll_number,
            'class_section' => optional($student->class_section)->class->name.' - '.optional($student->class_section)->section->name,
            'image' => $student->user->image ? asset('storage/'.$student->user->getRawOriginal('image')) : null,
        ];
    }
}
