<?php
require_once "model/ProductModel.php";
require_once "model/CategoriesModel.php";
require_once "view/helpers.php";

class HomeController {
    private $productModel;

    public function __construct() {
        $this->productModel = new ProductModel();
    }

    public function index() {
        $products = $this->productModel->getAllProducts();
        
        // Debug xem dữ liệu có được lấy không
        var_dump($products); 
        die();
    
        renderView("view/home.blade.php", compact('products'), "Home Page");
    }
    
    
}
?>
