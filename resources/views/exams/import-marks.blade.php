@extends('layouts.master')

@section('title')
    {{ __('import_marks') }}
@endsection

@section('content')
    <div class="content-wrapper">
        <div class="page-header">
            <h3 class="page-title">
                {{ __('import_marks') }}
            </h3>
            <div class="page-header-right">
                <a href="{{ route('exams.upload-marks') }}" class="btn btn-secondary">
                    <i class="fa fa-arrow-left"></i> {{ __('back') }}
                </a>
            </div>
        </div>

        <div class="row">
            {{-- Upload Form --}}
            <div class="col-md-12 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">{{ __('import_marks') }}</h4>

                        <form id="import-form" enctype="multipart/form-data">
                            @csrf
                            <div class="row">
                                <div class="form-group col-sm-12 col-md-6">
                                    <label>{{ __('class') }} <span class="text-danger">*</span></label>
                                    <select required name="class_id" id="class_id" class="form-control select2"
                                        style="width:100%;">
                                        <option value="">{{ __('select_class') }}</option>
                                        @foreach ($classes as $data)
                                            <option data-class-section-id="{{ $data->id }}"
                                                value="{{ $data->class->id }}">
                                                {{ $data->class->name }} - {{ $data->section->name }}
                                                {{ $data->class->medium->name }}
                                                {{ $data->class->streams->name ?? '' }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <input type="hidden" name="class_section_id" id="class_section_id">
                                </div>

                                <div class="form-group col-sm-12 col-md-6">
                                    <label>{{ __('exam') }} <span class="text-danger">*</span></label>
                                    <select required name="exam_id" id="exam_id" class="form-control select2"
                                        style="width:100%;">
                                        <option value="">{{ __('select') . ' ' . __('exam') }}</option>
                                    </select>
                                </div>

                                <div class="form-group col-sm-12 col-md-6">
                                    <label>{{ __('subject') }} <span class="text-danger">*</span></label>
                                    <select required name="subject_id" id="subject_id" class="form-control select2"
                                        style="width:100%;">
                                        <option value="">{{ __('select_subject') }}</option>
                                    </select>
                                </div>

                                <div class="form-group col-sm-12 col-md-6">
                                    <label>{{ __('file_upload') }} <span class="text-danger">*</span>
                                        <small class="text-muted">(CSV / XLSX)</small>
                                    </label>
                                    <input type="file" name="file" id="marks_file" class="form-control"
                                        accept=".csv,.xlsx,.xls" required />
                                </div>

                                <div class="col-sm-12 d-flex align-items-center gap-2 mt-2">
                                    <button type="submit" id="preview-btn" class="btn btn-theme">
                                        <i class="fa fa-eye"></i> {{ __('preview_import') }}
                                    </button>
                                    <a href="{{ route('exams.download-marks-template') }}" class="btn btn-outline-secondary ml-2">
                                        <i class="fa fa-download"></i> {{ __('download_sample_template') }}
                                    </a>
                                </div>
                            </div>
                        </form>

                        <div class="col-sm-12 mt-3">
                            <small class="text-muted">
                                <b>{{ __('Note') }}:</b> {{ __('import_marks_note') }}
                            </small>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Preview Section (hidden until file is parsed) --}}
            <div class="col-md-12 grid-margin stretch-card" id="preview-section" style="display:none;">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">{{ __('preview_import_results') }}</h4>

                        {{-- Valid rows --}}
                        <div id="valid-section">
                            <h5 class="text-success">
                                <i class="fa fa-check-circle"></i>
                                {{ __('valid_rows') }}: <span id="valid-count">0</span>
                            </h5>
                            <div class="table-responsive">
                                <table class="table table-bordered table-sm" id="valid-table">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>#</th>
                                            <th>{{ __('admission_no') }}</th>
                                            <th>{{ __('student_name') }}</th>
                                            <th>{{ __('obtained_marks') }}</th>
                                            <th>{{ __('total_marks') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody id="valid-tbody"></tbody>
                                </table>
                            </div>
                        </div>

                        {{-- Error rows --}}
                        <div id="error-section" class="mt-3">
                            <h5 class="text-danger">
                                <i class="fa fa-times-circle"></i>
                                {{ __('error_rows') }}: <span id="error-count">0</span>
                            </h5>
                            <div class="table-responsive">
                                <table class="table table-bordered table-sm" id="error-table">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>{{ __('row') }}</th>
                                            <th>{{ __('admission_no') }}</th>
                                            <th>{{ __('student_name') }}</th>
                                            <th>{{ __('marks_obtained') }}</th>
                                            <th>{{ __('error') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody id="error-tbody"></tbody>
                                </table>
                            </div>
                        </div>

                        {{-- Confirm Import button --}}
                        <div class="mt-3" id="confirm-section" style="display:none;">
                            <button type="button" id="confirm-import-btn" class="btn btn-success">
                                <i class="fa fa-check"></i> {{ __('confirm_import') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        // When class changes, update class_section_id and load exams
        $('#class_id').on('change', function () {
            const selectedOption = $(this).find('option:selected');
            const classSectionId = selectedOption.data('class-section-id');
            $('#class_section_id').val(classSectionId);

            // Reset dependent selects
            $('#exam_id').html('<option value="">{{ __('select') . ' ' . __('exam') }}</option>');
            $('#subject_id').html('<option value="">{{ __('select_subject') }}</option>');
            $('#preview-section').hide();

            const classId = $(this).val();
            if (!classId) return;

            $.ajax({
                url: '{{ route('exams.classes', ['class_id' => '__CLASS_ID__']) }}'.replace('__CLASS_ID__', classId),
                type: 'GET',
                success: function (response) {
                    if (response.error === false) {
                        let options = '<option value="">{{ __('select') . ' ' . __('exam') }}</option>';
                        $.each(response.data, function (i, exam) {
                            options += '<option value="' + exam.id + '">' + exam.name + '</option>';
                        });
                        $('#exam_id').html(options);
                    }
                }
            });
        });

        // When exam changes, load subjects
        $('#exam_id').on('change', function () {
            const classId = $('#class_id').val();
            const examId = $(this).val();

            $('#subject_id').html('<option value="">{{ __('select_subject') }}</option>');
            $('#preview-section').hide();

            if (!classId || !examId) return;

            $.ajax({
                url: '{{ route('exams.subject', ['class_id' => '__CID__', 'exam_id' => '__EID__']) }}'
                    .replace('__CID__', classId)
                    .replace('__EID__', examId),
                type: 'GET',
                success: function (response) {
                    if (response.error === false) {
                        let options = '<option value="">{{ __('select_subject') }}</option>';
                        $.each(response.data, function (i, timetable) {
                            options += '<option value="' + timetable.subject_id + '">' + timetable.subject.name + '</option>';
                        });
                        $('#subject_id').html(options);
                    }
                }
            });
        });

        // Handle preview form submit
        $('#import-form').on('submit', function (e) {
            e.preventDefault();

            const formData = new FormData(this);
            $('#preview-section').hide();

            $.ajax({
                url: '{{ route('exams.import-marks-preview') }}',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function (response) {
                    if (response.error) {
                        showErrorToast(response.message);
                        return;
                    }

                    renderPreview(response);
                },
                error: function () {
                    showErrorToast('{{ __('error_occurred') }}');
                }
            });
        });

        function renderPreview(response) {
            const validRows = response.valid || [];
            const errorRows = response.errors || [];

            // Valid rows
            $('#valid-count').text(validRows.length);
            let validHtml = '';
            validRows.forEach(function (row, i) {
                validHtml += '<tr>' +
                    '<td>' + (i + 1) + '</td>' +
                    '<td>' + escapeHtml(row.admission_no) + '</td>' +
                    '<td>' + escapeHtml(row.student_name) + '</td>' +
                    '<td>' + (row.obtained_marks !== null && row.obtained_marks !== undefined ? escapeHtml(String(row.obtained_marks)) : '') + '</td>' +
                    '<td>' + (row.total_marks !== null && row.total_marks !== undefined ? escapeHtml(String(row.total_marks)) : '') + '</td>' +
                    '</tr>';
            });
            $('#valid-tbody').html(validHtml || '<tr><td colspan="5" class="text-center text-muted">{{ __('no_valid_rows') }}</td></tr>');

            // Error rows
            $('#error-count').text(errorRows.length);
            let errorHtml = '';
            errorRows.forEach(function (row) {
                errorHtml += '<tr class="table-danger">' +
                    '<td>' + row.row + '</td>' +
                    '<td>' + escapeHtml(row.admission_no) + '</td>' +
                    '<td>' + escapeHtml(row.student_name) + '</td>' +
                    '<td>' + (row.marks_obtained !== null && row.marks_obtained !== undefined ? escapeHtml(String(row.marks_obtained)) : '') + '</td>' +
                    '<td>' + escapeHtml(row.error) + '</td>' +
                    '</tr>';
            });
            $('#error-tbody').html(errorHtml || '<tr><td colspan="5" class="text-center text-muted">{{ __('no_errors') }}</td></tr>');

            // Show confirm button only if there are valid rows
            if (validRows.length > 0) {
                $('#confirm-import-btn').data('valid-rows', validRows);
                $('#confirm-section').show();
            } else {
                $('#confirm-import-btn').data('valid-rows', []);
                $('#confirm-section').hide();
            }

            $('#preview-section').show();
        }

        // Confirm import – submit valid rows via existing submitMarks endpoint
        $('#confirm-import-btn').on('click', function () {
            const validRows = $(this).data('valid-rows') || [];
            if (validRows.length === 0) return;

            const examId = $('#exam_id').val();
            const classId = $('#class_id').val();
            const subjectId = $('#subject_id').val();

            if (!examId || !classId || !subjectId) {
                showErrorToast('{{ __('please_select_class_exam_and_subject') }}');
                return;
            }

            let data = {
                _token: '{{ csrf_token() }}',
                exam_id: examId,
                class_id: classId,
                subject_id: subjectId,
                exam_marks: []
            };

            validRows.forEach(function (row) {
                data.exam_marks.push({
                    student_id: row.student_id,
                    obtained_marks: row.obtained_marks,
                    total_marks: row.total_marks
                });
            });

            $(this).prop('disabled', true).text('{{ __('importing') }}...');

            $.ajax({
                url: '{{ route('exams.submit-marks') }}',
                type: 'POST',
                data: data,
                success: function (response) {
                    if (response.error) {
                        showErrorToast(response.message);
                        $('#confirm-import-btn').prop('disabled', false).html('<i class="fa fa-check"></i> {{ __('confirm_import') }}');
                    } else {
                        showSuccessToast(response.message);
                        // Reset the page
                        $('#import-form')[0].reset();
                        $('#preview-section').hide();
                        $('#exam_id').html('<option value="">{{ __('select') . ' ' . __('exam') }}</option>');
                        $('#subject_id').html('<option value="">{{ __('select_subject') }}</option>');
                        $('#confirm-import-btn').data('valid-rows', []);
                        $('#confirm-import-btn').prop('disabled', false).html('<i class="fa fa-check"></i> {{ __('confirm_import') }}');
                    }
                },
                error: function () {
                    showErrorToast('{{ __('error_occurred') }}');
                    $('#confirm-import-btn').prop('disabled', false).html('<i class="fa fa-check"></i> {{ __('confirm_import') }}');
                }
            });
        });

        function escapeHtml(str) {
            if (str === null || str === undefined) return '';
            return String(str)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }
    </script>
@endsection
