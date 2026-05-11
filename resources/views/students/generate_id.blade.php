@extends('layouts.master')

@section('title')
    {{ __('students') }}
@endsection

@section('content')
    <div class="content-wrapper">
        <div class="page-header">
            <h3 class="page-title">
                {{ __('manage') . ' ' . __('students') }}
            </h3>
        </div>
        <div class="row">
            <div class="col-lg-12 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">
                            {{ __('list') . ' ' . __('students') }}
                        </h4>
                        <div id="toolbar">
                            <div class="row">
                                <div class="col">
                                    <select name="filter_class_section_id" id="filter_class_section_id" class="form-control">
                                        <option value="">{{ __('select_class_section') }}</option>
                                        @foreach ($class_section as $class)
                                            <option value={{ $class->id }}>
                                                {{ $class->class->name . ' ' . $class->section->name . ' ' . $class->class->medium->name . ' ' . ($class->class->streams->name ?? ' ') }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12">
                                <table aria-describedby="mydesc" class='table' id='table_list' data-toggle="table" data-url="{{ url('students-list-data') }}" data-click-to-select="true" data-side-pagination="server" data-pagination="true" data-page-list="[5, 10, 20, 50, 100, 200 , 500]" data-search="true" data-toolbar="#toolbar" data-show-columns="true" data-show-refresh="true" data-fixed-columns="true" data-trim-on-search="false" data-mobile-responsive="true" data-sort-name="id" data-sort-order="desc" data-maintain-selected="true" data-export-types='["txt","excel"]' data-export-options='{ "fileName": "students-list-<?= date('d-m-y') ?>" ,"ignoreColumn":
                                    ["operate"]}' data-query-params="studentDetailsqueryParams"
                                    data-check-on-init="true" data-escape="true">

                                    <thead>
                                        <tr>
                                            <th data-field="state" data-checkbox="true"></th>
                                            <th data-field="id" data-sortable="true" data-visible="false">{{ __('id') }}</th>
                                            <th data-field="no" data-sortable="false">{{ __('no.') }}</th>
                                            <th data-field="user_id" data-sortable="false" data-visible="false">{{ __('user_id') }}</th>
                                            <th data-field="admission_no" data-sortable="false"> {{ __('gr_number') }}</th>
                                            <th data-field="class_section_id" data-sortable="false" data-visible="false">{{ __('class') . ' ' . __('section') . ' ' . __('id') }}</th>
                                            <th data-field="class_section_name" data-sortable="false">{{ __('class') . ' ' . __('section') }}</th>
                                            <th data-field="stream_name" data-sortable="false">{{ __('stream') }}</th>
                                            <th data-field="roll_number" data-sortable="false">{{ __('roll_no') }}</th>
                                            <th data-field="registration_payment_status" data-sortable="false" data-formatter="studentRegistrationPaymentStatusFormatter">Registration Payment</th>
                                            <th data-field="first_name" data-sortable="false">{{ __('first_name') }}</th>
                                            <th data-field="last_name" data-sortable="false">{{ __('last_name') }}</th>
                                            <th data-field="dob" data-sortable="false">{{ __('dob') }}</th>
                                            <th data-field="image" data-sortable="false" data-formatter="imageFormatter">{{ __('image') }}</th>
                                            <th data-field="father_first_name" data-sortable="false">{{ __('father') . ' ' . __('name') }}</th>
                                            <th data-field="father_mobile" data-sortable="false">{{ __('father') . ' ' . __('mobile') }}</th>
                                            <th data-field="guardian_first_name" data-sortable="false">{{ __('guardian') . ' ' . __('name') }}</th>
                                            <th data-field="guardian_mobile" data-sortable="false">{{ __('guardian') . ' ' . __('mobile') }}</th>
                                            <th data-field="current_address">{{ __('address') }}</th>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>
                        <div class="form-group col-12">
                            <form action="{{ url('generate-id-card') }}" target="_blank" method="post">
                                @csrf
                                <textarea id="user_id" name="user_id" style="display: none"></textarea>
                                <input type="submit" class="btn btn-theme mt-4" value="{{ __('Generate') }}">
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('script')
    <script>
        var $tableList = $('#table_list')
        var selections = []
        var user_list = [];

        @can('student-edit')
        // Override global formatter to make the badge a clickable toggle button
        function studentRegistrationPaymentStatusFormatter(value, row, index) {
            if (value === 'paid') {
                return "<button type='button' class='btn btn-xs btn-success toggle-reg-payment' data-id='" + row.id + "' data-status='paid' title='Click to mark as Unpaid'>Paid</button>";
            }
            return "<button type='button' class='btn btn-xs btn-danger toggle-reg-payment' data-id='" + row.id + "' data-status='unpaid' title='Click to mark as Paid'>Unpaid</button>";
        }

        $(document).on('click', '.toggle-reg-payment', function () {
            var $btn = $(this);
            var studentId = $btn.data('id');
            $.ajax({
                url: '{{ route("students.registration-payment-status.update", ":id") }}'.replace(':id', studentId),
                type: 'POST',
                data: { _token: '{{ csrf_token() }}' },
                beforeSend: function () { $btn.prop('disabled', true); },
                success: function (response) {
                    if (response.error) {
                        showErrorToast(response.message);
                    } else {
                        showSuccessToast(response.message);
                        $tableList.bootstrapTable('refresh');
                    }
                },
                error: function (xhr) {
                    var msg = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'An error occurred';
                    showErrorToast(msg);
                },
                complete: function () { $btn.prop('disabled', false); }
            });
        });
        @endcan

        function responseHandler(res) {
            $.each(res.rows, function(i, row) {
                row.state = $.inArray(row.id, selections) !== -1
            })
            return res
        }

        $(function() {
            $tableList.on('check.bs.table check-all.bs.table uncheck.bs.table uncheck-all.bs.table',
                function(e, rowsAfter, rowsBefore) {
                    user_list = [];
                    var rows = rowsAfter
                    if (e.type === 'uncheck-all') {
                        rows = rowsBefore
                    }
                    var ids = $.map(!$.isArray(rows) ? [rows] : rows, function(row) {
                        return row.id
                    })
                    var func = $.inArray(e.type, ['check', 'check-all']) > -1 ? 'union' : 'difference'
                    selections = window._[func](selections, ids)
                    selections.forEach(element => {
                        user_list.push(element);
                    });
                    $('textarea#user_id').val(user_list);
                })
        })
    </script>
@endsection
