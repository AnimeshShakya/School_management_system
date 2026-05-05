@extends('layouts.master')

@section('title')
    {{ __('students') }}
@endsection

@section('content')
    <div class="content-wrapper">
        <div class="page-header">
            <h3 class="page-title">
                {{ __('student') . ' ' . __('Result') }}
            </h3>
        </div>
        <div class="row">
            <div class="col-lg-12 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-start mb-4">
                            <div>
                                <h4 class="card-title mb-2">
                                    {{ __('generate') . ' ' . __('student') . ' ' . __('result') }}
                                </h4>
                                <p class="text-muted mb-0">Select a class section, review the available students, then open each final result as a printable PDF.</p>
                            </div>
                            <span class="badge badge-primary mt-3 mt-md-0 px-3 py-2">{{ $classes->count() }} class sections</span>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-4 grid-margin grid-margin-md-0 stretch-card">
                                <div class="card bg-light border-0 w-100">
                                    <div class="card-body py-3">
                                        <p class="text-muted mb-1">Current session</p>
                                        <h5 class="mb-0">Generate published results</h5>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 grid-margin grid-margin-md-0 stretch-card">
                                <div class="card bg-light border-0 w-100">
                                    <div class="card-body py-3">
                                        <p class="text-muted mb-1">Available actions</p>
                                        <h5 class="mb-0">Search, review, and export</h5>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 stretch-card">
                                <div class="card bg-light border-0 w-100">
                                    <div class="card-body py-3">
                                        <p class="text-muted mb-1">Selected students</p>
                                        <h5 class="mb-0"><span id="student-result-count">0</span> ready</h5>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="alert alert-info" role="alert">
                            <strong>Workflow:</strong> choose a class section first. The table will stay scoped to that section even when you search by name, roll number, or GR number.
                        </div>

                        @csrf
                        <div class="row align-items-end">
                            <div class="form-group col-sm-12 col-md-8 col-lg-6">
                                <label for="class_id">{{ __('class') }}<span class="text-danger">*</span></label><br>
                                <select required name="class_id" id="class_id" class="form-control select2" style="width:100%;" tabindex="-1" aria-hidden="true">
                                    <option value="">{{ __('select_class') }}</option>
                                    @foreach ($classes as $data)
                                        <option data-class-section-id="{{ $data->id }}" value="{{ $data->class->id }}">{{ $data->class->name }} - {{ $data->section->name }} {{ $data->class->medium->name }} {{ $data->class->streams->name ?? '' }}</option>
                                    @endforeach
                                </select>
                                <small class="text-muted d-block mt-2">Each generated result opens in a new PDF view using the exams already published for that student's class.</small>
                            </div>
                            <div class="form-group col-sm-12 col-md-4 col-lg-3">
                                <button type="button" id="search" class="btn btn-theme btn-block" disabled>{{ __('Search') }}</button>
                            </div>
                        </div>

                        <div id="result-empty-state" class="border rounded text-center py-5 px-4 bg-light mt-3">
                            <h5 class="mb-2">No class section selected</h5>
                            <p class="text-muted mb-0">Choose a class section to load the students eligible for result generation.</p>
                        </div>

                        <div class="show_student_list mt-4" style="display: none;">
                            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-3">
                                <div>
                                    <h5 class="mb-1">Student result queue</h5>
                                    <p class="text-muted mb-0">Use the search box to find a student quickly, then use the action button to generate the result PDF.</p>
                                </div>
                                <span class="badge badge-light mt-2 mt-md-0" id="selected-class-label">{{ __('class') }}: --</span>
                            </div>

                            <table aria-describedby="mydesc" class="table table-striped student_table" id="table_list" data-toggle="table" data-url="{{ route('get.student.list') }}" data-click-to-select="true" data-side-pagination="server" data-pagination="true" data-page-list="[10, 20, 50, 100, 200]" data-search="true" data-show-columns="true" data-show-refresh="true" data-fixed-columns="true" data-trim-on-search="false" data-mobile-responsive="true" data-sort-name="id" data-sort-order="desc" data-maintain-selected="true" data-export-types='["txt","excel"]' data-export-options='{ "fileName": "exam-result-list-<?= date('d-m-y') ?>" ,"ignoreColumn": ["operate"]}' data-query-params="uploadMarksqueryParams" data-toolbar="#toolbar" data-escape="true">
                                <thead>
                                    <tr>
                                        <th scope="col" data-field="id" data-sortable="true" data-visible="false">{{ __('id') }}</th>
                                        <th scope="col" data-field="no" data-sortable="false">{{ __('no.') }}</th>
                                        <th scope="col" data-field="student_name" data-sortable="true">{{ __('name') }}</th>
                                        <th scope="col" data-field="admission_no" data-sortable="false">{{ __('gr_number') }}</th>
                                        <th scope="col" data-field="roll_number" data-sortable="false">{{ __('roll_no') }}</th>
                                        <th scope="col" data-field="class_section_id" data-sortable="false" data-visible="false">{{ __('class') . ' ' . __('section') . ' ' . __('id') }}</th>
                                        <th scope="col" data-field="class_section_name" data-sortable="false">{{ __('class') . ' ' . __('section') }}</th>
                                        @canany(['generate-result'])
                                            <th scope="col" data-escape="false" data-events="studentEvents" data-width="150" data-field="operate" data-sortable="false">{{ __('action') }}</th>
                                        @endcanany
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('script')
<script>
    const classSelect = $('#class_id');
    const searchButton = $('#search');
    const resultTable = $('#table_list');
    const resultContainer = $('.show_student_list');
    const emptyState = $('#result-empty-state');
    const studentCount = $('#student-result-count');
    const selectedClassLabel = $('#selected-class-label');

    function syncSearchButtonState() {
        const hasClass = classSelect.val() !== '';

        searchButton.prop('disabled', !hasClass);
    }

    function selectedClassText() {
        return classSelect.find('option:selected').text().trim() || '--';
    }

    classSelect.on('change', function () {
        syncSearchButtonState();
        studentCount.text('0');
        selectedClassLabel.text('{{ __('class') }}: ' + selectedClassText());
        resultContainer.hide();
        emptyState.show();
    });

    searchButton.on('click', function () {
        if (!classSelect.val()) {
            showErrorToast('Please select a class section first.');
            return;
        }

        selectedClassLabel.text('{{ __('class') }}: ' + selectedClassText());
        emptyState.hide();
        resultContainer.show();
        resultTable.bootstrapTable('refresh', {silent: true});
    });

    resultTable.on('load-success.bs.table', function (e, response) {
        if (response.error == true) {
            showErrorToast(response.message);
            studentCount.text('0');
            resultContainer.hide();
            emptyState.show();
            return;
        }

        studentCount.text(response.total || 0);

        if ((response.total || 0) === 0) {
            emptyState.find('h5').text('No students found');
            emptyState.find('p').text('No students matched the selected class section and search filters.');
            emptyState.show();
        } else {
            emptyState.hide();
        }
    });

    resultTable.on('load-error.bs.table', function () {
        studentCount.text('0');
        resultContainer.hide();
        emptyState.find('h5').text('Unable to load students');
        emptyState.find('p').text('Please try again. If the issue persists, check your permissions or network connection.');
        emptyState.show();
        showErrorToast('Unable to load students for result generation.');
    });

    syncSearchButtonState();

</script>
@endsection

