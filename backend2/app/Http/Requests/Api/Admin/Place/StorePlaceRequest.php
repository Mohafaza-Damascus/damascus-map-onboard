<?php

namespace App\Http\Requests\Api\Admin\Place;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StorePlaceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // Place
            'title_ar' => ['required', 'string', 'max:255'],
            'title_en' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],

            'sub_category_id' => [
                'required',
                'integer',
                'exists:sub_categories,id',
            ],

            'admin_area_id' => [
                'nullable',
                'integer',
                'exists:admin_areas,id',
            ],

            'sector' => ['nullable', 'string', 'max:20'],

            'latitude' => [
                'required',
                'numeric',
                'between:' . config('damascus.bounds.latitude.min') . ',' .
                    config('damascus.bounds.latitude.max'),
            ],

            'longitude' => [
                'required',
                'numeric',
                'between:' . config('damascus.bounds.longitude.min') . ',' .
                    config('damascus.bounds.longitude.max'),
            ],

            'location_text' => ['nullable', 'string', 'max:255'],
            'opening_hours' => ['nullable', 'array'],
            'is_verified' => ['sometimes', 'boolean'],
            'is_visible' => ['sometimes', 'boolean'],

            // Communication Info
            'communication_info' => ['sometimes', 'array'],
            'communication_info.*.type' => [
                'required',
                'string',
                'in:phone,mobile,whatsapp,email,website,facebook,instagram,telegram',
            ],
            'communication_info.*.value' => [
                'required',
                'string',
            ],

            // Place Media
            'media' => ['sometimes', 'array'],
            'media.*.file_url' => [
                'required',
                'url:http,https',
                'max:500',
            ],
            'media.*.media_type' => [
                'required',
                'string',
                'in:image,video',
            ],
            'media.*.caption' => ['nullable', 'string'],
            'media.*.is_cover' => ['sometimes', 'boolean'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $this->validateCommunicationInfo($validator);
            $this->validateMedia($validator);
        });
    }

    private function validateCommunicationInfo(Validator $validator): void
    {
        foreach ($this->input('communication_info', []) as $index => $contact) {
            if (
                !is_array($contact) ||
                !isset($contact['type'], $contact['value']) ||
                !is_string($contact['type']) ||
                !is_string($contact['value'])
            ) {
                continue;
            }

            $type = $contact['type'];
            $value = trim($contact['value']);

            $valid = match ($type) {
                'phone' => preg_match('/^\d{7}$/', $value) === 1,

                'mobile', 'whatsapp' =>
                    preg_match('/^09\d{8}$/', $value) === 1,

                'email' =>
                    preg_match('/^[A-Za-z0-9._%+-]+@gmail\.com$/', $value) === 1,

                'website' => $this->isValidHttpUrl($value),

                'facebook' =>
                    $this->isValidSocialUrl($value, 'facebook.com'),

                'instagram' =>
                    $this->isValidSocialUrl($value, 'instagram.com'),

                'telegram' =>
                    $this->isValidSocialUrl($value, 't.me'),

                default => false,
            };

            if (!$valid) {
                $validator->errors()->add(
                    "communication_info.$index.value",
                    'قيمة معلومات التواصل غير صالحة لهذا النوع.'
                );
            }
        }
    }

    private function isValidHttpUrl(string $value): bool
    {
        if (!str_starts_with($value, 'http://') &&
            !str_starts_with($value, 'https://')) {
            return false;
        }

        return filter_var($value, FILTER_VALIDATE_URL) !== false;
    }

    private function isValidSocialUrl(string $value, string $domain): bool
    {
        if (!$this->isValidHttpUrl($value)) {
            return false;
        }

        $host = strtolower(parse_url($value, PHP_URL_HOST) ?? '');

        return $host === $domain ||
            str_ends_with($host, '.' . $domain);
    }

    private function validateMedia(Validator $validator): void
    {
        $media = $this->input('media', []);

        $coverCount = 0;

        foreach ($media as $index => $item) {
            if (!is_array($item)) {
                continue;
            }

            $isCover = filter_var(
                $item['is_cover'] ?? false,
                FILTER_VALIDATE_BOOLEAN
            );

            if (!$isCover) {
                continue;
            }

            $coverCount++;

            if (($item['media_type'] ?? null) !== 'image') {
                $validator->errors()->add(
                    "media.$index.is_cover",
                    'لا يمكن تعيين الفيديو كصورة غلاف.'
                );
            }
        }

        if ($coverCount > 1) {
            $validator->errors()->add(
                'media',
                'يمكن تحديد صورة غلاف واحدة فقط.'
            );
        }
    }
}
