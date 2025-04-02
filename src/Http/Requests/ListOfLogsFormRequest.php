<?php

declare(strict_types=1);

namespace Hryha\RequestLogger\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ListOfLogsFormRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'uri' => [
                'nullable',
                'string',
                'min:1',
                'max:1000',
            ],
            'excludeUris' => [
                'nullable',
                'array',
            ],
            'excludeUris.*' => [
                'string',
                'min:1',
                'max:1000',
            ],
            'methods' => [
                'nullable',
                'array',
            ],
            'methods.*' => [
                'string',
                'min:1',
                'max:20',
            ],
            'responseStatus' => [
                'nullable',
                'numeric',
                'min:0',
                'max:1000',
            ],
            'fingerprint' => [
                'nullable',
                'string',
                'min:1',
                'max:50',
            ],
            'excludeFingerprints' => [
                'nullable',
                'array',
            ],
            'excludeFingerprints.*' => [
                'string',
                'min:1',
                'max:50',
            ],
            'sentFrom' => [
                'nullable',
                'date',
            ],
            'sentTo' => [
                'nullable',
                'date',
            ],
            'durationFrom' => [
                'nullable',
                'numeric',
            ],
            'durationTo' => [
                'nullable',
                'numeric',
            ],
            'memoryFrom' => [
                'nullable',
                'numeric',
            ],
            'memoryTo' => [
                'nullable',
                'numeric',
            ],
            'customFields' => [
                'nullable',
                'array',
            ],
            'customFields.*' => [
                'string',
                'min:1',
                'max:1000',
            ],
            'orderBy' => [
                'string',
                Rule::in([
                    'sent',
                    'response_status',
                    'duration',
                    'memory',
                    'repeats'
                ])
            ],
            'orderDir' => [
                'string',
                Rule::in(['asc', 'desc']),
            ],
        ];
    }
}
