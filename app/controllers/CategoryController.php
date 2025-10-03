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

}