<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class QrAttendanceScanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->can('qr-attendance-scan');
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'qr_payload' => ['required', 'string', 'max:512'],
            'class_section_id' => ['required', 'integer', 'exists:class_sections,id'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'qr_payload.required' => 'QR code data is required.',
            'class_section_id.required' => 'Please select a class section.',
            'class_section_id.exists' => 'The selected class section is invalid.',
        ];
    }
}
