<?php
require_once "app/AppController.php";
require_once "app/AppResponse.php";
require_once "app/services/CategoryService.php";
require_once "app/dtos/category/request/CreateCategoryDto.php";
require_once "app/dtos/category/request/UpdateCategoryDto.php";
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
        $dto = new CreateCategoryDto($body);
        $errors = $dto->validate();
        if (is_array($errors)) {
            throw AppException::validationError($errors);
        }
        return AppResponse::success($this->categoryService->create($dto), "Categoría creada correctamente");
    }

    public function update($id)
    {
        $body = $this->body();
        $dto = new UpdateCategoryDto($body, $id);
        $errors = $dto->validate();
        if (is_array($errors)) {
            throw AppException::validationError($errors);
        }
        return AppResponse::success($this->categoryService->update($dto), "Categoría actualizada correctamente");
    }

    public function delete($id)
    {
        $this->categoryService->delete(intval($id));
        return AppResponse::success(message: "Categoría eliminada correctamente");
    }

}