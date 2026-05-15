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

    {{-- PIN Confirmation Modal --}}
    <div class="modal fade" id="pinModal" tabindex="-1" role="dialog" aria-labelledby="pinModalLabel" aria-hidden="true" data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog modal-dialog-centered" style="max-width:360px;" role="document">
            <div class="modal-content">
                <div class="modal-header" id="pinModalHeader">
                    <h5 class="modal-title" id="pinModalLabel">
                        <i class="fa fa-lock mr-2"></i>{{ __('confirm_with_pin') }}
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="{{ __('close') }}">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body text-center">
                    <p id="pin_action_desc" class="text-muted mb-3" style="font-size:0.9rem;"></p>

                    {{-- PIN dot display --}}
                    <div class="d-flex justify-content-center mb-3" style="gap:12px;">
                        <div class="pin-dot" id="dot1"></div>
                        <div class="pin-dot" id="dot2"></div>
                        <div class="pin-dot" id="dot3"></div>
                        <div class="pin-dot" id="dot4"></div>
                    </div>

                    <div id="pin_error_msg" class="alert alert-danger py-1 d-none" style="font-size:0.85rem;"></div>

                    {{-- Numeric keypad --}}
                    <div class="pin-keypad">
                        @foreach ([1,2,3,4,5,6,7,8,9] as $n)
                            <button type="button" class="btn btn-light pin-key" data-val="{{ $n }}">{{ $n }}</button>
                        @endforeach
                        <button type="button" class="btn btn-light pin-key" data-val="clear" id="pin_clear_btn">
                            <i class="fa fa-times"></i>
                        </button>
                        <button type="button" class="btn btn-light pin-key" data-val="0">0</button>
                        <button type="button" class="btn btn-light pin-key" data-val="backspace" id="pin_back_btn">
                            <i class="fa fa-backspace"></i>
                        </button>
                    </div>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('cancel') }}</button>
                    <button type="button" class="btn btn-primary" id="pin_confirm_btn" disabled>
                        <i class="fa fa-check mr-1"></i>{{ __('confirm') }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- QR Code Modal --}}
    <div class="modal fade" id="qrModal" tabindex="-1" role="dialog" aria-labelledby="qrModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" style="max-width:400px;" role="document">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title" id="qrModalLabel">
                        <i class="fa fa-qrcode mr-2"></i>{{ __('attendance_qr_code') }}
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="{{ __('close') }}">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body text-center p-4">
                    <p id="qr_student_name" class="font-weight-bold mb-1" style="font-size:1.1rem;"></p>
                    <p class="text-muted mb-3" style="font-size:0.85rem;">{{ __('payment_confirmed_qr_generated') }}</p>
                    <div class="d-flex justify-content-center mb-3">
                        <img id="qr_code_img" src="" alt="QR Code" style="width:220px;height:220px;border:1px solid #dee2e6;border-radius:8px;">
                    </div>
                    <a id="qr_download_btn" href="#" download class="btn btn-outline-success btn-sm">
                        <i class="fa fa-download mr-1"></i>{{ __('download_qr') }}
                    </a>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('close') }}</button>
                </div>
            </div>
        </div>
    </div>

    <style>
        .pin-dot {
            width: 16px;
            height: 16px;
            border-radius: 50%;
            border: 2px solid #6c757d;
            background: transparent;
            transition: background 0.15s;
        }
        .pin-dot.filled {
            background: #4e73df;
            border-color: #4e73df;
        }
        .pin-keypad {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 8px;
            max-width: 240px;
            margin: 0 auto;
        }
        .pin-key {
            height: 52px;
            font-size: 1.2rem;
            font-weight: 600;
            border-radius: 8px !important;
            border: 1px solid #dee2e6 !important;
        }
        .pin-key:hover {
            background: #e9ecef !important;
        }
        .pin-key:active {
            transform: scale(0.95);
        }
        #pinModalHeader {
            background: #f8f9fa;
            border-bottom: 2px solid #4e73df;
        }
    </style>
@endsection

@section('script')
    <script>
        var $tableList = $('#table_list');
        var selections = [];
        var user_list = [];

        // ── PIN state ──────────────────────────────────────────────────────
        var pinBuffer = '';
        var pendingStudentId = null;
        var pendingStudentName = '';
        var pendingCurrentStatus = '';

        function updatePinDots() {
            for (var i = 1; i <= 4; i++) {
                var $dot = $('#dot' + i);
                if (i <= pinBuffer.length) {
                    $dot.addClass('filled');
                } else {
                    $dot.removeClass('filled');
                }
            }
            $('#pin_confirm_btn').prop('disabled', pinBuffer.length < 4);
            $('#pin_error_msg').addClass('d-none').text('');
        }

        $(document).on('click', '.pin-key', function () {
            var val = $(this).data('val');
            if (val === 'clear') {
                pinBuffer = '';
            } else if (val === 'backspace') {
                pinBuffer = pinBuffer.slice(0, -1);
            } else {
                if (pinBuffer.length < 4) {
                    pinBuffer += String(val);
                }
            }
            updatePinDots();
        });

        // Also allow keyboard input when modal is open
        $('#pinModal').on('keydown', function (e) {
            if (e.key >= '0' && e.key <= '9') {
                if (pinBuffer.length < 4) { pinBuffer += e.key; updatePinDots(); }
            } else if (e.key === 'Backspace') {
                pinBuffer = pinBuffer.slice(0, -1); updatePinDots();
            } else if (e.key === 'Enter' && pinBuffer.length === 4) {
                $('#pin_confirm_btn').trigger('click');
            }
        });

        $('#pinModal').on('shown.bs.modal', function () {
            $(this).trigger('focus'); // capture keyboard
        }).on('hidden.bs.modal', function () {
            pinBuffer = '';
            updatePinDots();
            pendingStudentId = null;
        });

        // ── Registration payment toggle ────────────────────────────────────
        @can('student-edit')
        function studentRegistrationPaymentStatusFormatter(value, row) {
            if (value === 'paid') {
                return "<button type='button' class='btn btn-xs btn-success toggle-reg-payment' " +
                       "data-id='" + row.id + "' data-status='paid' data-name='" + _.escape(row.full_name || (row.first_name + ' ' + row.last_name)) + "' " +
                       "title='{{ __('click_to_mark_unpaid') }}'>" +
                       "<i class='fa fa-check-circle mr-1'></i>{{ __('paid') }}</button>";
            }
            return "<button type='button' class='btn btn-xs btn-danger toggle-reg-payment' " +
                   "data-id='" + row.id + "' data-status='unpaid' data-name='" + _.escape(row.full_name || (row.first_name + ' ' + row.last_name)) + "' " +
                   "title='{{ __('click_to_mark_paid') }}'>" +
                   "<i class='fa fa-times-circle mr-1'></i>{{ __('unpaid') }}</button>";
        }

        $(document).on('click', '.toggle-reg-payment', function () {
            var $btn = $(this);
            pendingStudentId  = $btn.data('id');
            pendingStudentName = $btn.data('name') || '';
            pendingCurrentStatus = $btn.data('status');

            var targetStatus = (pendingCurrentStatus === 'paid') ? '{{ __("unpaid") }}' : '{{ __("paid") }}';
            var colorClass   = (pendingCurrentStatus === 'paid') ? 'text-danger' : 'text-success';

            $('#pin_action_desc').html(
                '{{ __("changing_payment_status_for") }} <strong>' + _.escape(pendingStudentName) + '</strong><br>' +
                '{{ __("to") }}: <span class="font-weight-bold ' + colorClass + '">' + targetStatus + '</span>'
            );
            pinBuffer = '';
            updatePinDots();
            $('#pinModal').modal('show');
        });

        $('#pin_confirm_btn').on('click', function () {
            if (pinBuffer.length < 4 || pendingStudentId === null) { return; }

            var $btn = $(this);
            $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i>');

            $.ajax({
                url: '{{ route("students.registration-payment-status.update", ":id") }}'.replace(':id', pendingStudentId),
                type: 'POST',
                data: { _token: '{{ csrf_token() }}', pin: pinBuffer },
                success: function (response) {
                    if (response.error) {
                        $('#pin_error_msg').removeClass('d-none').text(response.message);
                        pinBuffer = '';
                        updatePinDots();
                    } else {
                        $('#pinModal').modal('hide');
                        showSuccessToast(response.message);
                        $tableList.bootstrapTable('refresh');

                        // Show QR code modal if student just became paid
                        if (response.status === 'paid' && response.qr_code_url) {
                            setTimeout(function () {
                                $('#qr_student_name').text(response.student_name || '');
                                var imgSrc = response.qr_code_url + '?t=' + Date.now();
                                $('#qr_code_img').attr('src', imgSrc);
                                $('#qr_download_btn').attr('href', imgSrc).attr('download', 'qr-' + (response.student_name || 'student').replace(/\s+/g, '-') + '.png');
                                $('#qrModal').modal('show');
                            }, 400);
                        }
                    }
                },
                error: function (xhr) {
                    var msg = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : '{{ __("error_occurred") }}';
                    $('#pin_error_msg').removeClass('d-none').text(msg);
                    pinBuffer = '';
                    updatePinDots();
                },
                complete: function () {
                    $btn.prop('disabled', pinBuffer.length < 4).html('<i class="fa fa-check mr-1"></i>{{ __("confirm") }}');
                }
            });
        });
        @endcan

        // ── Table selection → generate form ───────────────────────────────
        function responseHandler(res) {
            $.each(res.rows, function (i, row) {
                row.state = $.inArray(row.id, selections) !== -1;
            });
            return res;
        }

        $(function () {
            $tableList.on('check.bs.table check-all.bs.table uncheck.bs.table uncheck-all.bs.table',
                function (e, rowsAfter, rowsBefore) {
                    user_list = [];
                    var rows = rowsAfter;
                    if (e.type === 'uncheck-all') {
                        rows = rowsBefore;
                    }
                    var ids = $.map(!$.isArray(rows) ? [rows] : rows, function (row) {
                        return row.id;
                    });
                    var func = $.inArray(e.type, ['check', 'check-all']) > -1 ? 'union' : 'difference';
                    selections = window._[func](selections, ids);
                    selections.forEach(function (element) {
                        user_list.push(element);
                    });
                    $('textarea#user_id').val(user_list);
                });
        });
    </script>
@endsection

