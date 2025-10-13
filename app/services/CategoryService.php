<?php
require_once "app/models/CategoryModel.php";
require_once "app/exceptions/DBExceptionHandler.php";
class CategoryService
{
    public function getAll()
    {
        return CategoryModel::orderBy('created_at', 'asc')->get();
    }

    public function create(string $title)
    {
        try {
            return CategoryModel::create(['title' => $title]);
        } catch (Exception $e) {
            throw new DBExceptionHandler($e, [
                ["name" => "title_UNIQUE", "message" => "Ya existe una categoría con este título"]
            ]);
        }
    }

    public function update(int $id, string $newTitle)
    {
        try {
            $category = CategoryModel::find($id);
            if (empty($category)) {
                throw AppException::badRequest("La categoría seleccionada no existe");
            }
            $category->update(['title' => $newTitle]);
            return $category->fresh();
        } catch (Exception $e) {
            if (get_class($e) === "AppException") {
                throw $e;
            }
            throw new DBExceptionHandler($e, [
                ["name" => "title_UNIQUE", "message" => "Ya existe una categoría con este título"]
            ]);
        }
    }

    public function delete(int $id)
    {
        try {
            $category = CategoryModel::find($id);
            if (empty($category)) {
                throw AppException::badRequest("La categoría seleccionada no existe");
            }

            $category->delete();

        } catch (Exception $e) {
            if (get_class($e) === "AppException") {
                throw $e;
            }
            throw new DBExceptionHandler($e, [
                ["name" => "fk_section_items_category", "message" => "No se puede eliminar la categoría porque está asociada a uno o más ítems de sección"]
            ]);
        }

    }
}