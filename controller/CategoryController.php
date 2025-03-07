<?php
require_once "model/CategoriesModel.php";
require_once "view/helpers.php";

class CategoryController {
    private $categoryModel;

    public function __construct() {
        $this->categoryModel = new CategoryModel();
    }

    public function index() {
        $categories = $this->categoryModel->getAllCategories();
        renderView("view/categories/category_list.blade.php", compact('categories'), "Category List");
    }

    public function show($id) {
        $category = $this->categoryModel->getCategoryById($id);
        renderView("view/categories/category_detail.blade.php", compact('category'), "Category Detail");
    }

    public function create() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = $_POST['name'];
            $description = $_POST['description'];

            $this->categoryModel->createCategory($name, $description);
            header("Location: /categories");
        } else {
            renderView("view/categories/category_create.blade.php", [], "Create Category");
        }
    }

    public function edit($id) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = $_POST['name'];
            $description = $_POST['description'];

            $this->categoryModel->updateCategory($id, $name, $description);
            header("Location: /categories");
        } else {
            $category = $this->categoryModel->getCategoryById($id);
            renderView("view/categories/category_edit.blade.php", compact('category'), "Edit Category");
        }
    }

    public function delete($id) {
        $this->categoryModel->deleteCategory($id);
        header("Location: /categories");
    }
}