<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Result || {{ config('app.name') }}</title>
    <style>
        @page {
            margin: 22px;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            margin: 0;
            padding: 0;
            color: #1f2933;
            font-size: 12px;
            line-height: 1.4;
        }

        * {
            box-sizing: border-box;
        }

        .document {
            width: 100%;
            border: 1.5px solid {{ $settings['theme_color'] ?? '#22577a' }};
            padding: 18px;
        }

        .header-table,
        .info-table,
        .result-table,
        .summary-table,
        .footer-table {
            width: 100%;
            border-collapse: collapse;
        }

        .header-table td {
            vertical-align: middle;
        }

        .brand-block {
            text-align: center;
        }

        .logo {
            height: 58px;
            width: auto;
            margin-bottom: 8px;
        }

        .school-name {
            font-size: 24px;
            font-weight: 700;
            color: {{ $settings['theme_color'] ?? '#22577a' }};
        }

        .school-address {
            font-size: 11px;
            color: {{ $settings['secondary_color'] ?? '#38A3A5' }};
        }

        .title-block {
            margin: 18px 0 14px;
            padding: 12px 14px;
            border: 1px solid {{ $settings['theme_color'] ?? '#22577a' }};
            background: #f8fbfc;
            text-align: center;
        }

        .title-block h1 {
            margin: 0;
            font-size: 18px;
            color: {{ $settings['theme_color'] ?? '#22577a' }};
        }

        .title-block p {
            margin: 4px 0 0;
            font-size: 11px;
            color: #52606d;
        }

        .section-label {
            margin: 16px 0 8px;
            font-size: 12px;
            font-weight: 700;
            color: {{ $settings['theme_color'] ?? '#22577a' }};
            text-transform: uppercase;
            letter-spacing: 0.4px;
        }

        .info-table td,
        .summary-table td {
            width: 50%;
            border: 1px solid #d9e2ec;
            padding: 9px 10px;
            vertical-align: top;
        }

        .label {
            display: block;
            font-size: 10px;
            color: #7b8794;
            text-transform: uppercase;
            margin-bottom: 2px;
        }

        .value {
            font-size: 12px;
            font-weight: 600;
            color: #102a43;
        }

        .result-table {
            margin-top: 8px;
            font-size: 11px;
        }

        .result-table th, .result-table td {
            border: 1px solid #d9e2ec;
            padding: 9px 8px;
            text-align: center;
        }

        .result-table th {
            background-color: {{ $settings['theme_color'] ?? '#22577a' }};
            color: #ffffff;
            font-size: 10px;
            font-weight: 700;
        }

        .result-table .subject-column {
            text-align: left;
            font-weight: 700;
            width: 24%;
        }

        .result-table tbody tr:nth-child(even) {
            background-color: #f8fbfc;
        }

        .summary-table {
            margin-top: 14px;
        }

        .result-pill {
            font-weight: 700;
            color: {{ $data['result'] === 'Passed' ? '#0b6e4f' : '#b42318' }};
        }

        .footer-table {
            margin-top: 28px;
        }

        .footer-table td {
            width: 50%;
            vertical-align: bottom;
        }

        .generated-note {
            font-size: 10px;
            color: #7b8794;
        }

        .signature-block {
            text-align: right;
        }

        .signature-image {
            height: 42px;
            width: auto;
            margin-bottom: 6px;
        }

        .signature-label {
            display: inline-block;
            border-top: 1px solid #9fb3c8;
            padding-top: 6px;
            min-width: 110px;
            text-align: center;
            font-weight: 600;
        }

        .muted {
            color: #7b8794;
        }
    </style>

</head>
<body>
    <div class="document">
        <table class="header-table">
            <tr>
                <td>
                    <div class="brand-block">
                        @if (!empty($settings['logo1']))
                            <img class="logo" src="{{ public_path('storage/') . $settings['logo1'] }}" alt="School logo">
                        @endif
                        <div class="school-name">{{ $settings['school_name'] }}</div>
                        <div class="school-address">{{ $settings['school_address'] }}</div>
                    </div>
                </td>
            </tr>
        </table>

        <div class="title-block">
            <h1>{{ $data['class_section'] }} Result Sheet</h1>
            <p>Session Year: {{ $data['sessionYear'] }} | Generated on {{ $data['date'] }}</p>
        </div>

        <div class="section-label">Student Information</div>
        <table class="info-table">
            <tr>
                <td>
                    <span class="label">Student Name</span>
                    <span class="value">{{ $data['student_name'] }}</span>
                </td>
                <td>
                    <span class="label">Father / Guardian</span>
                    <span class="value">{{ $data['guardian_name'] ?: '-' }}</span>
                </td>
            </tr>
            <tr>
                <td>
                    <span class="label">Date of Birth</span>
                    <span class="value">{{ $data['dob'] }}</span>
                </td>
                <td>
                    <span class="label">GR Number</span>
                    <span class="value">{{ $data['gr_no'] }}</span>
                </td>
            </tr>
            <tr>
                <td>
                    <span class="label">Class Section</span>
                    <span class="value">{{ $data['class_section'] }}</span>
                </td>
                <td>
                    <span class="label">Roll Number</span>
                    <span class="value">{{ $data['roll_number'] ?: '-' }}</span>
                </td>
            </tr>
        </table>

        <div class="section-label">Subject Performance</div>
        <table class="result-table">
            <thead>
                <tr>
                    <th class="subject-column">Subject</th>
                    @foreach ($exams as $exam)
                        @if (!empty($exam->timetable))
                            <th>{{ $exam->name }}</th>
                        @endif
                    @endforeach
                    <th>Total</th>
                    <th>Grade</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($data['subjects'] as $subject => $subjectData)
                    <tr>
                        <td class="subject-column">{{ $subject }}</td>
                        @foreach ($exams as $exam)
                            <td>
                                @if (isset($subjectData[$exam->name]))
                                    {{ $subjectData[$exam->name] }}
                                @else
                                    -
                                @endif
                            </td>
                        @endforeach
                        <td>{{ $subjectData['total_obtained'] ?? '-' }} / {{ $subjectData['total_marks'] ?? '-' }}</td>
                        <td>{{ $subjectData['grade'] ?? '-' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="section-label">Overall Summary</div>
        <table class="summary-table">
            <tr>
                <td>
                    <span class="label">Marks Secured</span>
                    <span class="value">{{ $data['obtainmarks'] }} / {{ $data['totalMarks'] }}</span>
                </td>
                <td>
                    <span class="label">Percentage</span>
                    <span class="value">{{ $data['percentage'] !== null ? $data['percentage'] . '%' : '-' }}</span>
                </td>
            </tr>
            <tr>
                <td>
                    <span class="label">Overall Grade</span>
                    <span class="value">{{ $data['grade'] ?: '-' }}</span>
                </td>
                <td>
                    <span class="label">Result</span>
                    <span class="value result-pill">{{ $data['result'] ?: '-' }}</span>
                </td>
            </tr>
        </table>

        <table class="footer-table">
            <tr>
                <td>
                    <div class="generated-note">This is a computer-generated academic result prepared for school records and printing.</div>
                </td>
                <td>
                    <div class="signature-block">
                        @if (!empty($settings['signature']))
                            <img class="signature-image" src="{{ public_path('storage/') . $settings['signature'] }}" alt="Signature">
                        @endif
                        <div class="signature-label">Authorized Signature</div>
                    </div>
                </td>
            </tr>
        </table>
    </div>
</body>
</html>
