<?php
require_once 'app/AppException.php';
require_once 'app/utils/FileUploader.php';

class ValidationEngine
{
    private $errors = [];
    private $data = [];

    public function __construct($data)
    {

        if (is_object($data)) {
            $this->data = get_object_vars($data);
        } elseif (is_array($data)) {
            $this->data = $data;
        } else {
            throw AppException::internalServer('Invalid data type for validation');
        }
    }

    public function required($field, $message = null)
    {
        // Verify if the field exists and is not empty in the object
        if (
            !array_key_exists($field, $this->data) ||
            $this->isEmpty($this->data[$field])
        ) {

            $this->errors[$field] = $message ?? "$field is required";
        }
        return $this;
    }

    public function optional($field)
    {
        // If the field does not exist or is empty, remove any existing errors for this field
        if (
            !array_key_exists($field, $this->data) ||
            $this->isEmpty($this->data[$field])
        ) {
            unset($this->errors[$field]);
        }
        return $this;
    }

    public function email($field, $message = null)
    {
        if (
            array_key_exists($field, $this->data) &&
            !$this->isEmpty($this->data[$field]) &&
            !filter_var($this->data[$field], FILTER_VALIDATE_EMAIL)
        ) {

            $this->errors[$field] = $message ?? "$field must be a valid email";
        }
        return $this;
    }

    public function minLength($field, $length, $message = null)
    {
        if (
            array_key_exists($field, $this->data) &&
            !$this->isEmpty($this->data[$field]) &&
            strlen($this->data[$field]) < $length
        ) {

            $this->errors[$field] = $message ?? "$field must be at least $length characters";
        }
        return $this;
    }

    public function min($field, int|float $minValue, $message = null)
    {
        if (
            array_key_exists($field, $this->data) &&
            !$this->isEmpty($this->data[$field]) &&
            is_numeric($this->data[$field]) &&
            $this->data[$field] < $minValue
        ) {

            $this->errors[$field] = $message ?? "$field must be at least $minValue";
        }
        return $this;
    }

    public function maxLength($field, $length, $message = null)
    {
        if (
            array_key_exists($field, $this->data) &&
            !$this->isEmpty($this->data[$field]) &&
            strlen($this->data[$field]) > $length
        ) {

            $this->errors[$field] = $message ?? "$field must not exceed $length characters";
        }
        return $this;
    }


    public function fieldsMatchInArray($fields, $array, $message = null)
    {
        foreach ($fields as $field) {
            if (
                array_key_exists($field, $this->data) &&
                !$this->isEmpty($this->data[$field]) &&
                (!is_array($array) || !in_array($this->data[$field], $array))
            ) {
                $this->errors[$field] = $message ?? "$field must match one of the specified values";
            }
        }
        return $this;
    }



    public function in($field, $values, $message = null)
    {
        if (
            array_key_exists($field, $this->data) &&
            !$this->isEmpty($this->data[$field]) &&
            !in_array($this->data[$field], $values)
        ) {

            $this->errors[$field] = $message ?? "$field must be one of: " . implode(', ', $values);
        }
        return $this;
    }

    public function enum($field, $enumClass, $message = null)
    {
        if (
            array_key_exists($field, $this->data) &&
            !$this->isEmpty($this->data[$field]) &&
            !in_array($this->data[$field], array_map(fn($case) => $case->value, $enumClass::cases()))
        ) {
            $enumValues = array_map(fn($case) => $case->value, $enumClass::cases());
            $this->errors[$field] = $message ?? "$field must be one of: " . implode(', ', $enumValues);
        }
        return $this;
    }

    public function array($field, $message = null)
    {
        if (
            array_key_exists($field, $this->data) &&
            !$this->isEmpty($this->data[$field]) &&
            !is_array($this->data[$field])
        ) {

            $this->errors[$field] = $message ?? "$field must be an array";
        }
        return $this;
    }


    public function minItems($field, $minItems, $message = null)
    {
        if (
            array_key_exists($field, $this->data) &&
            !$this->isEmpty($this->data[$field]) &&
            is_array($this->data[$field]) &&
            count($this->data[$field]) < $minItems
        ) {

            $this->errors[$field] = $message ?? "$field must have at least $minItems items";
        }
        return $this;
    }

    public function numeric($field, $message = null)
    {
        if (
            array_key_exists($field, $this->data) &&
            !$this->isEmpty($this->data[$field]) &&
            !is_numeric($this->data[$field])
        ) {

            $this->errors[$field] = $message ?? "$field must be numeric";
        }
        return $this;
    }

    public function boolean($field, $message = null)
    {
        if (
            array_key_exists($field, $this->data) &&
            !$this->isEmpty($this->data[$field]) &&
            !is_bool($this->data[$field]) &&
            !in_array($this->data[$field], [0, 1, '0', '1'], true)
        ) {

            $this->errors[$field] = $message ?? "$field must be a boolean";
        }
        return $this;
    }

    public function integer($field, $message = null)
    {
        if (
            array_key_exists($field, $this->data) &&
            !$this->isEmpty($this->data[$field]) && (!is_numeric($this->data[$field]) ||
                !filter_var($this->data[$field], FILTER_VALIDATE_INT))
        ) {

            $this->errors[$field] = $message ?? "$field must be an integer";
        }
        return $this;
    }

    public function unique($field, $model, $excludeId = null, $message = null)
    {
        if (array_key_exists($field, $this->data) && !$this->isEmpty($this->data[$field])) {
            $exists = $model->findByField($field, $this->data[$field]);
            if ($exists && (!$excludeId || $exists['id'] != $excludeId)) {
                $this->errors[$field] = $message ?? "$field already exists";
            }
        }
        return $this;
    }

    public function pattern($field, $pattern, $message = null)
    {
        if (
            array_key_exists($field, $this->data) &&
            !$this->isEmpty($this->data[$field]) &&
            !preg_match($pattern, $this->data[$field])
        ) {

            $this->errors[$field] = $message ?? "$field format is invalid";
        }
        return $this;
    }

    public function files($field, $allowExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'], $message = null)
    {
        $fileUploader = new FileUploader();

        $validation = $fileUploader->validateFile($this->data[$field] ?? null, $allowExtensions);
        if (is_string($validation)) {
            $this->errors[$field] = $message ?? $validation;
        }

        return $this;
    }



    private function isEmpty($value)
    {
        return $value === null ||
            $value === '' ||
            (is_string($value) && trim($value) === '');
    }

    public function fails()
    {
        return !empty($this->errors);
    }

    public function passes()
    {
        return empty($this->errors);
    }

    public function getErrors()
    {
        return $this->errors;
    }

    public function validate()
    {
        if ($this->fails()) {
            throw AppException::validationError("Validation failed", $this->errors);
        }
        return true;
    }
}
?>