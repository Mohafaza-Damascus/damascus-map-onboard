<?php

namespace App\Http\Requests\Api\Admin\Place;

class UpdatePlaceRequest extends StorePlaceRequest
{
    public function rules(): array
    {
        $rules = parent::rules();

        // PATCH: جميع حقول المكان تصبح اختيارية.
        // PUT: تبقى الحقول الأساسية مطلوبة.
        if ($this->isMethod('patch')) {
            $optionalFields = [
                'title_ar',
                'title_en',
                'description',
                'sub_category_id',
                'admin_area_id',
                'sector',
                'latitude',
                'longitude',
                'location_text',
                'opening_hours',
                'is_verified',
                'is_visible',
            ];

            foreach ($optionalFields as $field) {
                if (isset($rules[$field])) {
                    $rules[$field] = array_values(array_filter(
                        $rules[$field],
                        fn ($rule) => $rule !== 'required'
                    ));

                    array_unshift($rules[$field], 'sometimes');
                }
            }
        }

        return $rules;
    }
}
