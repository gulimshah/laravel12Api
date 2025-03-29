<?php

namespace App\Rules;

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class Base64Image implements Rule
{
    public function passes($attribute, $value)
    {
        // Ensure the string contains "data:image/"
        if (!str_starts_with($value, 'data:image/')) {
            return false;
        }

        // Extract the image type and base64 data
        [$metaData, $imageData] = explode(',', $value, 2) + [null, null];
        if (!$metaData || !$imageData) {
            return false;
        }

        // Validate the image type (png, jpg, jpeg, gif, bmp, webp)
        $allowedTypes = ['png', 'jpg', 'jpeg'];
        $imageType = str_replace(['data:image/', ';base64'], '', $metaData);

        if (!in_array($imageType, $allowedTypes)) {
            return false;
        }

        // Validate base64 encoding
        return base64_decode($imageData, true) !== false;
    }

    public function message()
    {
        return 'The :attribute must be a valid base64-encoded image.';
    }
}
