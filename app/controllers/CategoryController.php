<?php
require_once "app/AppController.php";
require_once "app/AppResponse.php";
require_once "app/services/CategoryService.php";
class CategoryController extends AppController
{

    private CategoryService $categoryService;
    public function __construct()
    {
        $this->categoryService = new CategoryService();
    }

    public function getAll()
    {
        $categories = $this->categoryService->getAll();
        return AppResponse::success($categories);
    }

    public function create()
    {
        $body = $this->body();
        $title = $body['title'] ?? null;
        if (empty($title)) {
            throw AppException::validationError("El título es obligatorio");
        }
        return AppResponse::success($this->categoryService->create($title), "Categoría creada correctamente");
    }

    public function update($id)
    {
        $body = $this->body();
        $newTitle = $body['title'] ?? null;
        if (empty($newTitle)) {
            throw AppException::validationError("El título es obligatorio");
        }
        return AppResponse::success($this->categoryService->update(intval($id), $newTitle), "Categoría actualizada correctamente");
    }

    public function delete($id)
    {
        $this->categoryService->delete(intval($id));
        return AppResponse::success(message: "Categoría eliminada correctamente");
    }

}