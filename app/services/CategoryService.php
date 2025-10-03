<?php
require_once "app/models/CategoryModel.php";
class CategoryService
{
    public function getAll()
    {
        return CategoryModel::all();
    }
}