<?php
require_once "model/ProductModel.php";
require_once "model/CategoriesModel.php";
require_once "view/helpers.php";
session_start();
class ProductController {
    private $productModel;
    private $categoryModel;

    public function __construct() {
        $this->productModel = new ProductModel();
        $this->categoryModel = new CategoryModel();
    }

    public function index() {
        $products = $this->productModel->getAllProducts();
        renderView("view/products/product_list.blade.php", compact('products'), "Product List");
    }
        // Phương thức mới cho sản phẩm người dùng
        public function userProductList() {
            // Lấy tham số tìm kiếm và lọc theo danh mục
            $search = isset($_GET['search']) ? $_GET['search'] : '';
            $categoryId = isset($_GET['category']) ? $_GET['category'] : '';
            $sort = isset($_GET['sort']) ? $_GET['sort'] : '';
        
            // Lọc sản phẩm theo tên và danh mục
            $products = $this->productModel->getProductsByFilter($search, $categoryId, $sort);
        
            // Lấy danh sách các danh mục để hiển thị trên form lọc
            $categories = $this->categoryModel->getAllCategories();
        
            renderView("view/products/product_list_user.blade.php", [
                'products' => $products,
                'categories' => $categories,
                'sort' => $sort          
            ], "Product List for Users");
        }
        

        public function show($id) {
            // Lấy thông tin sản phẩm
            $product = $this->productModel->getProductById($id);
            $productVariants = $this->productModel->getProductVariants($id);
            $subImages = $this->productModel->getSubImages($id);
        
            if (!$product) {
                die("Sản phẩm không tồn tại!");
            }
        
            // Lấy danh sách sản phẩm liên quan (cùng category nhưng không phải sản phẩm hiện tại)
            $relatedProducts = $this->productModel->getRelatedProducts($product['category_id'], $id);
        
            // Render trang chi tiết sản phẩm
            renderView("view/products/product_detail.blade.php", [
                'product' => $product,
                'productVariants' => $productVariants,
                'subImages' => $subImages,
                'relatedProducts' => $relatedProducts
            ], "Product Detail");
        }
        
    

    public function create() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = $_POST['name'];
            $description = $_POST['description'];
            $price = $_POST['price'];
            $category_id = $_POST['category_id'];
            $image_url = null;

            // Kiểm tra và xử lý tải lên hình ảnh
            if (isset($_FILES["image"]) && $_FILES["image"]["error"] == 0) {
                $target_dir = "uploads/";
                if (!file_exists($target_dir)) {
                    mkdir($target_dir, 0777, true);
                }
                $image_url = $target_dir . basename($_FILES["image"]["name"]);
                move_uploaded_file($_FILES["image"]["tmp_name"], $image_url);
            }

            $this->productModel->createProduct($name, $description, $price, $category_id, $image_url);
            header("Location: /products");
            exit;
        } else {
            $categories = $this->categoryModel->getAllCategories();
            renderView("view/products/product_create.blade.php", compact('categories'), "Create Product");
        }
    }

    public function edit($id) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = $_POST['name'];
            $description = $_POST['description'];
            $price = $_POST['price'];
            $category_id = $_POST['category_id'];
            $image_url = $_POST['existing_image']; // Ảnh cũ

            // Kiểm tra nếu có ảnh mới được tải lên
            if (isset($_FILES["image"]) && $_FILES["image"]["error"] == 0) {
                $target_dir = "uploads/";
                if (!file_exists($target_dir)) {
                    mkdir($target_dir, 0777, true);
                }
                $image_url = $target_dir . basename($_FILES["image"]["name"]);
                move_uploaded_file($_FILES["image"]["tmp_name"], $image_url);
            }

            $this->productModel->updateProduct($id, $name, $description, $price, $category_id, $image_url);
            header("Location: /products");
            exit;
        } else {
            $product = $this->productModel->getProductById($id);
            $categories = $this->categoryModel->getAllCategories();
            renderView("view/products/product_edit.blade.php", compact('product', 'categories'), "Edit Product");
        }
    }
    public function delete($id) {
        $this->productModel->deleteProduct($id);
        header("Location: /products");
        exit;
    }

    //subimage
    public function showProductDetail($productId) {
        // Lấy thông tin sản phẩm
        $product = $this->productModel->getProductById($productId);
    
        // Lấy ảnh phụ của sản phẩm
        $subImages = $this->productModel->getSubImages($productId);
    
        // Truyền dữ liệu vào view
        renderView("view/products/product_detail.blade.php", [
            'product' => $product,
            'subImages' => $subImages
        ], "Product Detail");
    }
    
    public function showSubImages($productId) {
        $product = $this->productModel->getProductById($productId); // Lấy thông tin sản phẩm
        $subImages = $this->productModel->getSubImages($productId); // Lấy các ảnh phụ của sản phẩm
    
        renderView("view/products/product_sub_images.blade.php", [
            'product' => $product,
            'subImages' => $subImages
        ], "Product Sub Images");
    }
    public function createSubImage($productId) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Lấy dữ liệu từ form
            $image = $_FILES['image']['name'];
            $targetDir = "uploads/sub_images/";
            $targetFile = $targetDir . basename($image);
    
            // Di chuyển ảnh từ tạm thời sang thư mục lưu trữ
            if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFile)) {
                // Gọi phương thức model để tạo ảnh phụ cho sản phẩm
                $this->productModel->createSubImage($productId, $image);
                header("Location: /products/$productId/sub-images"); // Quay lại trang danh sách ảnh phụ
            } else {
                echo "Lỗi khi tải lên ảnh.";
            }
        } else {
            renderView("view/products/product_sub_images_create.blade.php", ['productId' => $productId], "Create Product Sub Image");
        }
    }
    public function editSubImage($productId, $subImageId) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Lấy dữ liệu từ form
            $image = $_FILES['image']['name'];
            $targetDir = "uploads/sub_images/";
            $targetFile = $targetDir . basename($image);
    
            // Di chuyển ảnh từ tạm thời sang thư mục lưu trữ
            if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFile)) {
                // Gọi phương thức model để cập nhật ảnh phụ
                $this->productModel->updateSubImage($subImageId, $image);
                header("Location: /products/$productId/sub-images"); // Quay lại trang danh sách ảnh phụ
            } else {
                echo "Lỗi khi tải lên ảnh.";
            }
        } else {
            // Lấy thông tin ảnh phụ từ model để điền vào form chỉnh sửa
            $subImage = $this->productModel->getSubImageById($subImageId);
            renderView("view/products/product_sub_images_edit.blade.php", [
                'productId' => $productId,
                'subImage' => $subImage
            ], "Edit Product Sub Image");
        }
    }
    public function deleteSubImage($productId, $subImageId) {
        // Gọi phương thức model để xóa ảnh phụ
        $this->productModel->deleteSubImage($subImageId);
        header("Location: /products/$productId/sub-images"); // Quay lại trang danh sách ảnh phụ
    }

    
}
