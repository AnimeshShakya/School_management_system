<?php

namespace Tests\Feature;

use App\Models\ClassSchool;
use App\Models\ClassSection;
use App\Models\Medium;
use App\Models\Section;
use App\Models\SessionYear;
use App\Models\Settings;
use App\Models\Students;
use App\Models\User;
use Database\Seeders\AttendeeTeacherRoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class QrAttendanceTest extends TestCase
{
    use RefreshDatabase;

    private User $attendeeTeacher;

    private User $unauthorizedUser;

    private Students $student;

    private ClassSection $classSection;

    private SessionYear $sessionYear;

    private string $validPayload;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(AttendeeTeacherRoleSeeder::class);

        $medium = Medium::create(['name' => 'English', 'status' => 1]);
        $class = ClassSchool::create(['name' => 'Class 1', 'medium_id' => $medium->id]);
        $section = Section::create(['name' => 'A']);

        $this->classSection = ClassSection::create([
            'class_id' => $class->id,
            'section_id' => $section->id,
            'medium_id' => $medium->id,
        ]);

        $this->sessionYear = SessionYear::create([
            'name' => '2025-2026',
            'start_date' => '2025-04-01',
            'end_date' => '2026-03-31',
            'status' => 1,
            'default' => 1,
        ]);

        Settings::create(['type' => 'session_year', 'message' => (string) $this->sessionYear->id]);

        $studentUser = User::factory()->create(['status' => 1]);
        $this->student = Students::create([
            'user_id' => $studentUser->id,
            'class_id' => $class->id,
            'class_section_id' => $this->classSection->id,
            'admission_no' => 'ADM001',
        ]);

        $this->student->refresh();

        $this->validPayload = base64_encode(json_encode([
            's' => $this->student->id,
            't' => $this->student->qr_token,
        ]));

        $this->attendeeTeacher = User::factory()->create(['status' => 1]);
        $role = Role::findByName('Attendee Teacher', 'web');
        $this->attendeeTeacher->assignRole($role);

        $this->unauthorizedUser = User::factory()->create(['status' => 1]);
    }

    public function test_attendee_teacher_can_access_qr_scanner_page(): void
    {
        $response = $this->actingAs($this->attendeeTeacher)
            ->get(route('qr-attendance.index'));

        $response->assertOk();
        $response->assertViewIs('qr_attendance.index');
    }

    public function test_unauthorized_user_is_redirected_from_qr_scanner(): void
    {
        $response = $this->actingAs($this->unauthorizedUser)
            ->get(route('qr-attendance.index'));

        $response->assertRedirect(route('home'));
    }

    public function test_valid_qr_scan_marks_attendance_as_present(): void
    {
        $response = $this->actingAs($this->attendeeTeacher)
            ->postJson(route('qr-attendance.scan'), [
                'qr_payload' => $this->validPayload,
                'class_section_id' => $this->classSection->id,
            ]);

        $response->assertOk()
            ->assertJson(['error' => false])
            ->assertJsonPath('student.admission_no', 'ADM001');

        $this->assertDatabaseHas('attendances', [
            'student_id' => $this->student->id,
            'class_section_id' => $this->classSection->id,
            'type' => 1,
        ]);

        $this->assertDatabaseHas('qr_attendance_logs', [
            'student_id' => $this->student->id,
            'status' => 'present',
        ]);
    }

    public function test_duplicate_qr_scan_returns_warning(): void
    {
        $this->actingAs($this->attendeeTeacher)
            ->postJson(route('qr-attendance.scan'), [
                'qr_payload' => $this->validPayload,
                'class_section_id' => $this->classSection->id,
            ]);

        $response = $this->actingAs($this->attendeeTeacher)
            ->postJson(route('qr-attendance.scan'), [
                'qr_payload' => $this->validPayload,
                'class_section_id' => $this->classSection->id,
            ]);

        $response->assertOk()
            ->assertJson(['error' => false, 'warning' => true]);

        $this->assertDatabaseCount('attendances', 1);
    }

    public function test_invalid_qr_token_is_rejected(): void
    {
        $tamperedPayload = base64_encode(json_encode([
            's' => $this->student->id,
            't' => str_repeat('a', 64),
        ]));

        $response = $this->actingAs($this->attendeeTeacher)
            ->postJson(route('qr-attendance.scan'), [
                'qr_payload' => $tamperedPayload,
                'class_section_id' => $this->classSection->id,
            ]);

        $response->assertStatus(422)
            ->assertJson(['error' => true]);

        $this->assertDatabaseCount('attendances', 0);
    }

    public function test_malformed_qr_payload_is_rejected(): void
    {
        $response = $this->actingAs($this->attendeeTeacher)
            ->postJson(route('qr-attendance.scan'), [
                'qr_payload' => 'not-a-valid-base64-json',
                'class_section_id' => $this->classSection->id,
            ]);

        $response->assertStatus(422)
            ->assertJson(['error' => true]);
    }

    public function test_unauthorized_user_cannot_scan_qr(): void
    {
        $response = $this->actingAs($this->unauthorizedUser)
            ->postJson(route('qr-attendance.scan'), [
                'qr_payload' => $this->validPayload,
                'class_section_id' => $this->classSection->id,
            ]);

        $response->assertStatus(403);
    }

    public function test_attendee_teacher_can_view_history(): void
    {
        $response = $this->actingAs($this->attendeeTeacher)
            ->get(route('qr-attendance.history'));

        $response->assertOk();
        $response->assertViewIs('qr_attendance.history');
    }

    public function test_student_gets_qr_token_on_creation(): void
    {
        $this->assertNotNull($this->student->qr_token);
        $this->assertEquals(64, strlen($this->student->qr_token));
    }
}
