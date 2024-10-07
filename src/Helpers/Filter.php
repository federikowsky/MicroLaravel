<?php

namespace App\Helpers;

class Filter
{
    protected $sanitizer;
    protected $validator;

    public function __construct(Sanitizer $sanitizer, Validator $validator)
    {
        $this->sanitizer = $sanitizer;
        $this->validator = $validator;
    }

    /**
     * Filter and validate input data.
     *
     * @param array $data
     * @param array $fields
     * @param array $messages
     * @return array
     */
    public function filter(array $data, array $fields, array $messages = []): array
    {
        $sanitization_rules = [];
        $validation_rules = [];

        foreach ($fields as $field => $rules) {
            if (strpos($rules, '|')) {
                [$sanitization_rules[$field], $validation_rules[$field]] = explode('|', $rules, 2);
            } else {
                $sanitization_rules[$field] = $rules;
            }
        }

        $inputs = $this->sanitizer->sanitize($data, $sanitization_rules);
        $errors = $this->validator->validate($inputs, $validation_rules, $messages);

        return [$inputs, $errors];
    }
}
