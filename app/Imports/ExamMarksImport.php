<?php

declare(strict_types=1);

namespace App\Imports;

use App\Models\ExamTimetable;
use App\Models\Students;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ExamMarksImport implements SkipsEmptyRows, ToCollection, WithHeadingRow
{
    public array $valid = [];

    public array $errors = [];

    private int $classSectionId;

    private float $totalMarks;

    public function __construct(int $classSectionId, int $classId, int $examId, int $subjectId)
    {
        $this->classSectionId = $classSectionId;

        $examTimetable = ExamTimetable::where([
            'exam_id'    => $examId,
            'class_id'   => $classId,
            'subject_id' => $subjectId,
        ])->first();

        $this->totalMarks = $examTimetable ? (float) $examTimetable->total_marks : 0;
    }

    public function collection(Collection $rows): void
    {
        $seenAdmissionNos = [];

        foreach ($rows as $index => $row) {
            $rowNum      = $index + 2; // row 1 is the heading
            $admissionNo = isset($row['admission_no']) ? trim((string) $row['admission_no']) : '';
            $marksRaw    = $row['marks_obtained'] ?? null;

            // Validate admission_no present
            if ($admissionNo === '') {
                $this->errors[] = [
                    'row'          => $rowNum,
                    'admission_no' => $admissionNo,
                    'student_name' => $row['student_name'] ?? '',
                    'marks_obtained' => $marksRaw,
                    'error'        => trans('admission_no_is_required'),
                ];
                continue;
            }

            // Validate no duplicate admission numbers in this file
            if (in_array($admissionNo, $seenAdmissionNos, true)) {
                $this->errors[] = [
                    'row'          => $rowNum,
                    'admission_no' => $admissionNo,
                    'student_name' => $row['student_name'] ?? '',
                    'marks_obtained' => $marksRaw,
                    'error'        => trans('duplicate_admission_no_in_file'),
                ];
                continue;
            }

            // Validate student exists in the given class section
            $student = Students::with('user')
                ->where('admission_no', $admissionNo)
                ->where('class_section_id', $this->classSectionId)
                ->first();

            if ($student === null) {
                $this->errors[] = [
                    'row'          => $rowNum,
                    'admission_no' => $admissionNo,
                    'student_name' => $row['student_name'] ?? '',
                    'marks_obtained' => $marksRaw,
                    'error'        => trans('admission_no_not_found_in_class'),
                ];
                continue;
            }

            // Validate marks_obtained is numeric and non-negative
            if ($marksRaw === null || $marksRaw === '' || ! is_numeric($marksRaw) || (float) $marksRaw < 0) {
                $this->errors[] = [
                    'row'          => $rowNum,
                    'admission_no' => $admissionNo,
                    'student_name' => $student->user->first_name . ' ' . $student->user->last_name,
                    'marks_obtained' => $marksRaw,
                    'error'        => trans('marks_obtained_must_be_a_non_negative_number'),
                ];
                continue;
            }

            // Validate marks do not exceed total marks
            if ((float) $marksRaw > $this->totalMarks) {
                $this->errors[] = [
                    'row'          => $rowNum,
                    'admission_no' => $admissionNo,
                    'student_name' => $student->user->first_name . ' ' . $student->user->last_name,
                    'marks_obtained' => $marksRaw,
                    'error'        => trans('marks_obtained_exceeds_total_marks') . ' (' . $this->totalMarks . ')',
                ];
                continue;
            }

            $seenAdmissionNos[] = $admissionNo;

            $this->valid[] = [
                'student_id'     => $student->id,
                'student_name'   => $student->user->first_name . ' ' . $student->user->last_name,
                'admission_no'   => $admissionNo,
                'obtained_marks' => (float) $marksRaw,
                'total_marks'    => $this->totalMarks,
            ];
        }
    }
}
