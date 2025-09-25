<?php
class PageExceptionHandler extends Exception
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
        if (stripos($e->getMessage(), "unique_menu_id")) {

            $this->message = "No se puede asignar el mismo menú a dos páginas.";
        } else {
            $this->message = "Error de base de datos: " . $e->getMessage();
        }
    }
}
?>