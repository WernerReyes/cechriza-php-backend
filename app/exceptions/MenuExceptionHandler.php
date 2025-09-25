<?php
class MenuExceptionHandler extends Exception
{

    public function __construct(Exception $e)
    {
        if ($e instanceof PDOException) {
            $this->DBExceptions($e);

        }
        parent::__construct($this->message);

    }

    private function DBExceptions(Exception $e)
    {
        if (stripos($e->getMessage(), "unique_order_per_parent")) {

            $this->message = "No puede haber dos menús con el mismo orden.";
        } else {
            $this->message = "Error de base de datos: " . $e->getMessage();
        }
    }
}
?>