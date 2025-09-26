<?php
class DBExceptionHandler extends Exception
{

    public function __construct(Exception $e, array $constraints)
    {

        $this->handle($e, $constraints);
    }

    private function handle(Exception $e, array $constraints)
    {
        foreach ($constraints as $constraint) {
            if (stripos($e->getMessage(), $constraint["name"]) !== false) {
                return parent::__construct($constraint["message"] ?? $e->getMessage());
            }
        }
        return parent::__construct("Database error: " . $e->getMessage());

    }


}
?>