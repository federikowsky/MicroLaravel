<?php

namespace App\Helpers;

class Sanitizer
{
    const FILTERS = [
        'string' => 0,
        'string[]' => [
            'filters' => 0,
            'flags' => FILTER_REQUIRE_ARRAY
        ],
        'email' => FILTER_SANITIZE_EMAIL,
        'int' => [
            'filters' => FILTER_SANITIZE_NUMBER_INT,
            'flags' => FILTER_REQUIRE_SCALAR
        ],
        'int[]' => [
            'filters' => FILTER_SANITIZE_NUMBER_INT,
            'flags' => FILTER_REQUIRE_ARRAY
        ],
        'float' => [
            'filters' => FILTER_SANITIZE_NUMBER_FLOAT,
            'flags' => FILTER_FLAG_ALLOW_FRACTION
        ],
        'float[]' => [
            'filters' => FILTER_SANITIZE_NUMBER_FLOAT,
            'flags' => FILTER_REQUIRE_ARRAY
        ],
        'url' => FILTER_SANITIZE_URL,
        
    ];

    /**
     * Recursively trim strings in an array
     * @param array $items
     * @return array
     */
    public function array_trim(array $items): array
    {
        return array_map(function ($item) {
            if (is_string($item)) {
                return trim($item);
            } elseif (is_array($item)) {
                return $this->array_trim($item);
            } else
                return $item;
        }, $items);
    }

    /**
     * Get the options and flags for the filter_var function
     * @param string $type
     * @param array $filters
     * @param string $item
     * @return int|string
     */
    public function get_filter_options(string $type, array $filters, string $item)
    {
        if (isset($filters[$type])) {
            if (is_array($filters[$type])) {
                if (is_array($filters[$type][$item]))
                    return implode('|', $filters[$type][$item]);
                return $filters[$type][$item];
            } else if ($item === "filters")
                return $filters[$type];
            return 0;
        }
        return FILTER_UNSAFE_RAW; // Default to FILTER_UNSAFE_RAW
    }

    /**
     * Sanitize the inputs based on the rules
     * @param array $inputs
     * @param array $fields
     * @param bool $trim
     * @return array
     */
    public function sanitize(array $inputs, array $fields = [], bool $trim = true): array
    {
        $data = [];

        foreach ($inputs as $key => $value) {
            $fieldType = trim($fields[$key]) ?? 'string'; // Default to 'string' if not defined

            if ($fieldType === 'string')
                $data[$key] = htmlspecialchars($value);
            else if ($fieldType === 'string[]') 
                $data[$key] = array_map('htmlspecialchars', $value);
            else if (isset(self::FILTERS[$fieldType])) {
                $options = $this->get_filter_options($fieldType, self::FILTERS, "filters");
                $flags = $this->get_filter_options($fieldType, self::FILTERS, "flags");
                $data[$key] = filter_var($value, $options, $flags);
            }
        }

        return $trim ? $this->array_trim($data) : $data;
    }
}
