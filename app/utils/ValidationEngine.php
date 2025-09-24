<?php
require_once 'app/exceptions/ApiException.php';

class ValidationEngine
{
    private $errors = [];
    private $data = [];

    public function __construct($data)
    {
        // Convertir objeto a array si es necesario
        error_log("<--PROBANDO--> " . json_encode($data) . ' ' . is_object($data) . ' ' . json_encode(get_object_vars($data)));


        if (is_object($data)) {
            $this->data = get_object_vars($data);
        } else {
            $this->data = is_array($data) ? $data : [];
        }
    }

    public function required($field, $message = null)
    {
        // Verificar si el campo existe en el array Y no está vacío
        if (
            !array_key_exists($field, $this->data) ||
            $this->isEmpty($this->data[$field])
        ) {

            $this->errors[$field] = $message ?? "$field is required";
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

    public function min($field, $length, $message = null)
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

    public function max($field, $length, $message = null)
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
            throw ApiException::validationError("Validation failed", $this->errors);
        }
        return true;
    }
}
?>