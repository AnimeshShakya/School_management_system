<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\ClassSection;
use App\Models\QrAttendanceLog;
use App\Models\Students;
use App\Services\ResponseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class QrAttendanceApiController extends Controller
{
    public function login(Request $request): void
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            ResponseService::validationError($validator->errors()->first());
        }

        if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            $auth = Auth::user();

            if (! $auth->hasRole('Attendee Teacher')) {
                ResponseService::errorResponse('Invalid Login Credentials', null, 101);
            }

            $token = $auth->createToken($auth->first_name)->plainTextToken;

            if ($request->fcm_id) {
                $auth->fcm_id = $request->fcm_id;
                $auth->save();
            }
            if ($request->device_type) {
                $auth->device_type = $request->device_type;
                $auth->save();
            }

            $user = [
                'id' => $auth->id,
                'first_name' => $auth->first_name,
                'last_name' => $auth->last_name,
                'email' => $auth->email,
                'image' => $auth->image,
            ];

            ResponseService::successResponse('User logged-in!', $user, ['token' => $token], 100);
        } else {
            ResponseService::errorResponse('Invalid Login Credentials', null, 101);
        }
    }

    /**
     * Scan a QR code payload and record attendance (mobile app endpoint).
     */
    public function scan(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'qr_payload' => ['required', 'string', 'max:512'],
            'class_section_id' => ['required', 'integer', 'exists:class_sections,id'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => true,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        if (! Auth::user()->can('qr-attendance-scan')) {
            return response()->json([
                'error' => true,
                'message' => trans('no_permission_message'),
            ], 403);
        }

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

            if (! $student->registration_payment_status) {
                return response()->json(['error' => true, 'message' => 'Registration payment is pending. This student\'s QR card is not active.'], 403);
            }

            $sessionYear = getSettings('session_year');
            $sessionYearId = (int) $sessionYear['session_year'];
            $classSectionId = (int) $request->class_section_id;
            $date = now()->toDateString();

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
                    'type' => 1,
                    'date' => $date,
                    'remark' => 'QR scan (mobile)',
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
            Log::error('QR attendance API scan failed', ['error' => $th->getMessage()]);

            return response()->json(['error' => true, 'message' => 'An error occurred while processing the QR code.'], 500);
        }
    }

    /**
     * Return the class sections available for QR scanning.
     */
    public function classSections(): JsonResponse
    {
        if (! Auth::user()->can('qr-attendance-scan')) {
            return response()->json(['error' => true, 'message' => trans('no_permission_message')], 403);
        }

        $sections = ClassSection::with('class:id,name', 'section:id,name')
            ->get(['id', 'class_id', 'section_id']);

        return response()->json(['error' => false, 'data' => $sections]);
    }

    /**
     * Decode and validate the base64-encoded JSON QR payload.
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
        ];
    }
}
