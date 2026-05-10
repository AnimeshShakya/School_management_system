@extends('layouts.master')

@section('title')
    {{ __('QR Attendance Scanner') }}
@endsection

@section('content')
    <div class="content-wrapper">
        <div class="page-header">
            <h3 class="page-title">
                <span class="page-title-icon bg-gradient-primary text-white me-2">
                    <i class="fa fa-qrcode"></i>
                </span>
                {{ __('QR Code Attendance') }}
            </h3>
        </div>

        <div class="row">
            {{-- Scanner Panel --}}
            <div class="col-md-6 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">{{ __('Scan Student QR Code') }}</h4>

                        <div class="form-group">
                            <label for="class_section_id">
                                {{ __('class') }} {{ __('section') }}
                                <span class="text-danger">*</span>
                            </label>
                            <select id="class_section_id" class="form-control select2" style="width:100%;">
                                <option value="">{{ __('select') . ' ' . __('class') }}</option>
                                @foreach ($classSections as $section)
                                    <option value="{{ $section->id }}">
                                        {{ $section->class->name }} - {{ $section->section->name }}
                                        {{ $section->class->medium->name }}
                                        {{ $section->class->streams->name ?? '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Camera QR Scanner --}}
                        <div id="reader" style="width:100%; min-height:250px;" class="mt-3 border rounded"></div>

                        <div class="mt-3">
                            <button id="btn-start-scan" class="btn btn-gradient-primary btn-sm me-2">
                                <i class="fa fa-camera"></i> {{ __('Start Camera') }}
                            </button>
                            <button id="btn-stop-scan" class="btn btn-gradient-danger btn-sm" style="display:none;">
                                <i class="fa fa-stop"></i> {{ __('Stop Camera') }}
                            </button>
                        </div>

                        <hr>

                        {{-- Manual QR Payload Input --}}
                        <h6 class="mt-3">{{ __('Or enter QR payload manually') }}</h6>
                        <div class="input-group">
                            <input type="text" id="manual_qr" class="form-control" placeholder="Paste QR code data...">
                            <div class="input-group-append">
                                <button id="btn-manual-submit" class="btn btn-gradient-info">
                                    <i class="fa fa-check"></i> {{ __('Submit') }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Result Panel --}}
            <div class="col-md-6 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">{{ __('Scan Result') }}</h4>

                        <div id="scan-result-area">
                            <div class="text-center text-muted py-5">
                                <i class="fa fa-qrcode fa-4x mb-3 d-block"></i>
                                <p>{{ __('Scan a student QR code to record attendance.') }}</p>
                            </div>
                        </div>

                        <hr>

                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <h5 class="mb-0">{{ __("Today's Scans") }}</h5>
                            <a href="{{ route('qr-attendance.history') }}" class="btn btn-outline-secondary btn-sm">
                                <i class="fa fa-history"></i> {{ __('Full History') }}
                            </a>
                        </div>

                        <div id="recent-scans" class="mt-3">
                            <p class="text-muted small">{{ __('Recent scans will appear here.') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>

    <script>
        const SCAN_URL = "{{ route('qr-attendance.scan') }}";
        const CSRF_TOKEN = "{{ csrf_token() }}";

        let html5QrCode = null;
        let scanInProgress = false;
        const recentScans = [];

        function getClassSectionId() {
            return document.getElementById('class_section_id').value;
        }

        function showResult(data, type = 'success') {
            const area = document.getElementById('scan-result-area');
            const alertClass = type === 'success' ? 'alert-success' :
                               type === 'warning' ? 'alert-warning' : 'alert-danger';
            const icon = type === 'success' ? 'fa-check-circle' :
                         type === 'warning' ? 'fa-exclamation-triangle' : 'fa-times-circle';

            let studentHtml = '';
            if (data.student) {
                const img = data.student.image
                    ? `<img src="${data.student.image}" class="rounded-circle" width="60" height="60" alt="">`
                    : `<span class="badge bg-secondary" style="font-size:2rem;padding:14px 18px;"><i class="fa fa-user"></i></span>`;

                studentHtml = `
                    <div class="d-flex align-items-center mt-2">
                        <div class="me-3">${img}</div>
                        <div>
                            <strong>${data.student.name}</strong><br>
                            <small class="text-muted">
                                Admission: ${data.student.admission_no} &nbsp;|&nbsp;
                                Roll: ${data.student.roll_number ?? 'N/A'}<br>
                                ${data.student.class_section}
                            </small>
                        </div>
                    </div>`;
            }

            area.innerHTML = `
                <div class="alert ${alertClass}" role="alert">
                    <i class="fa ${icon}"></i> ${data.message}
                    ${studentHtml}
                </div>`;

            if (data.student && type === 'success') {
                prependRecentScan(data.student);
            }
        }

        function prependRecentScan(student) {
            recentScans.unshift(student);

            const container = document.getElementById('recent-scans');
            const list = recentScans.slice(0, 10).map(s =>
                `<div class="d-flex align-items-center py-1 border-bottom">
                    <i class="fa fa-check-circle text-success me-2"></i>
                    <span><strong>${s.name}</strong> <small class="text-muted">(${s.admission_no})</small></span>
                </div>`
            ).join('');

            container.innerHTML = list || '<p class="text-muted small">No scans yet.</p>';
        }

        async function submitScan(qrPayload) {
            if (scanInProgress) { return; }
            const classSectionId = getClassSectionId();

            if (!classSectionId) {
                showResult({ message: 'Please select a class section first.' }, 'error');
                return;
            }

            scanInProgress = true;

            try {
                const response = await fetch(SCAN_URL, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': CSRF_TOKEN,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ qr_payload: qrPayload, class_section_id: classSectionId }),
                });

                const result = await response.json();

                if (result.error) {
                    showResult(result, 'error');
                } else if (result.warning) {
                    showResult(result, 'warning');
                } else {
                    showResult(result, 'success');
                }
            } catch (e) {
                showResult({ message: 'Network error. Please try again.' }, 'error');
            } finally {
                // Brief cooldown to prevent rapid re-scans of the same code
                setTimeout(() => { scanInProgress = false; }, 3000);
            }
        }

        document.getElementById('btn-start-scan').addEventListener('click', function () {
            if (!getClassSectionId()) {
                alert('Please select a class section before scanning.');
                return;
            }

            html5QrCode = new Html5Qrcode('reader');
            html5QrCode.start(
                { facingMode: 'environment' },
                { fps: 10, qrbox: { width: 250, height: 250 } },
                (decodedText) => { submitScan(decodedText); },
                (errorMessage) => { /* silent scan errors */ }
            ).then(() => {
                document.getElementById('btn-start-scan').style.display = 'none';
                document.getElementById('btn-stop-scan').style.display = 'inline-block';
            }).catch(err => {
                showResult({ message: 'Camera access denied or unavailable: ' + err }, 'error');
            });
        });

        document.getElementById('btn-stop-scan').addEventListener('click', function () {
            if (html5QrCode) {
                html5QrCode.stop().then(() => {
                    document.getElementById('btn-start-scan').style.display = 'inline-block';
                    document.getElementById('btn-stop-scan').style.display = 'none';
                });
            }
        });

        document.getElementById('btn-manual-submit').addEventListener('click', function () {
            const payload = document.getElementById('manual_qr').value.trim();
            if (!payload) {
                showResult({ message: 'Please enter a QR payload.' }, 'error');
                return;
            }
            submitScan(payload);
        });

        document.getElementById('manual_qr').addEventListener('keydown', function (e) {
            if (e.key === 'Enter') {
                document.getElementById('btn-manual-submit').click();
            }
        });
    </script>
@endsection
