<?php
require_once "model/ProductModel.php";
require_once "model/ProductVariantsModel.php";
require_once "model/ColorModel.php";
require_once "model/SizeModel.php";
require_once "view/helpers.php";

class ProductVariantController
{
    private $productModel;
    private $sizeModel;
    private $colorModel;
    private $productVariantModel;

    public function __construct()
    {
        $this->productModel = new ProductModel();
        $this->sizeModel = new SizeModel();
        $this->colorModel = new ColorModel();
        $this->productVariantModel = new ProductVariantModel();
    }

    public function index()
    {
        $products = $this->productModel->getAllProducts();
        //compact: gom bien dien thanh array
        renderView("view/products/product_list.blade.php", compact('products'), "Product List");
    }

    // Trong ProductVariantController.php
    public function show($id)
    {
        $product = $this->productModel->getProductById($id);
        $productVariants = $this->productVariantModel->getVariantByProductId($id);
        $subImages = $this->productModel->getSubImages($id);

        // Chuyển đổi variants thành format phù hợp
        $variantsData = [];
        foreach ($productVariants as $variant) {
            $variantsData[$variant['color'] . '_' . $variant['size']] = [
                'id' => $variant['id'],
                'quantity' => $variant['quantity'],
                'price' => $variant['price']
            ];
        }

        $variantsJson = json_encode($variantsData);

        renderView(
            "view/products/product_detail.blade.php",
            compact('product', 'productVariants', 'variantsJson', 'subImages'),
            "Chi tiết sản phẩm"
        );
    }

    public function create($id)
    {
        $message = "";
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $errors = [];
            $product_id = $_POST['product_id'];
            $colorId = $_POST['colorId'];
            $sizeId = $_POST['sizeId'];
            $image = $_POST['image'];
            $quantity = $_POST['quantity'];
            $price = $_POST['price'];
            $sku = $_POST['sku'];
            if ($this->productVariantModel->checkExistSku($sku)) {
                $errors[] = "Sku is already exist";
                $products = $this->productModel->getAllProducts();
                $sizes = $this->sizeModel->getAll();
                $colors = $this->colorModel->getAll();
                renderView("view/productsvariant/create.blade.php", compact("products", "colors", "sizes", "errors"), "Create ProductVariants");
            }

            $this->productVariantModel->createVariants($product_id, $colorId, $sizeId, $image, $quantity, $price, $sku);
            $message = "<p class='alert alert-primary '>Create product variant successfully</p>";
            $_SESSION['message'] = $message;
            header("Location: /products");
        } else {
            $products = $this->productModel->getAllProducts();
            $sizes = $this->sizeModel->getAll();
            $colors = $this->colorModel->getAll();

            renderView("view/productsvariant/create.blade.php", compact("products", "colors", "sizes"), "Create ProductVariants");
        }
    }

    // public function edit($id) {
    //     if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    //         $name = $_POST['name'];
    //         $description = $_POST['description'];
    //         $price = $_POST['price'];

    //         $this->productModel->updateProduct($id, $name, $description, $price);
    //         header("Location: /products");
    //     } else {
    //         $product = $this->productModel->getProductById($id);
    //         renderView("view/product_edit.php", compact('product'), "Edit Product");
    //     }
    // }
    public function listVariants($product_id)
    {
        $product = $this->productModel->getProductById($product_id);
        $productVariants = $this->productVariantModel->getVariantByProductId($product_id);

        renderView("view/productsvariant/list.blade.php", compact("product", "productVariants"), "Danh sách biến thể sản phẩm");
    }
    public function delete($id) {
        // if ($this->productVariantModel->deleteVariant($id)) {
        //     $_SESSION['success_message'] = "Xóa biến thể sản phẩm thành công!";
        // } else {
        //     $_SESSION['error_message'] = "Lỗi khi xóa biến thể sản phẩm.";
        // }
        $this->productVariantModel->deleteVariant($id);
        header("Location: /products"); // Chuyển hướng về danh sách biến thể sản phẩm
        exit;
    }
    public function edit($id) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $colorId = $_POST['colorId'];
                $sizeId = $_POST['sizeId'];
                $quantity = $_POST['quantity'];
                $price = $_POST['price'];
                $sku = $_POST['sku'];
                
                // Kiểm tra SKU trùng
                $currentVariant = $this->productVariantModel->getVariantById($id);
                if ($sku !== $currentVariant['sku'] && $this->productVariantModel->checkExistSku($sku)) {
                    $_SESSION['error_message'] = "SKU đã tồn tại";
                    header("Location: /product-variants/edit/" . $id);
                    exit;
                }
    
                // Xử lý ảnh nếu có
                $image = $currentVariant['image']; // Giữ ảnh cũ mặc định
                if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
                    $uploadDir = "uploads/variants/";
                    if (!file_exists($uploadDir)) {
                        mkdir($uploadDir, 0777, true);
                    }
                    
                    $imageFileType = strtolower(pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION));
                    $newFileName = uniqid() . '.' . $imageFileType;
                    $targetFile = $uploadDir . $newFileName;
    
                    if (move_uploaded_file($_FILES["image"]["tmp_name"], $targetFile)) {
                        $image = $targetFile;
                    }
                }
                
                $result = $this->productVariantModel->updateVariant($id, $colorId, $sizeId, $image, $quantity, $price, $sku);
                
                if ($result) {
                    $_SESSION['success_message'] = "Cập nhật biến thể thành công";
                    header("Location: /products");
                    exit;
                } else {
                    $_SESSION['error_message'] = "Có lỗi xảy ra khi cập nhật biến thể";
                    header("Location: /product-variants/edit/" . $id);
                    exit;
                }
    
            } catch (Exception $e) {
                $_SESSION['error_message'] = $e->getMessage();
                header("Location: /product-variants/edit/" . $id);
                exit;
            }
        } else {
            // Hiển thị form edit
            $variant = $this->productVariantModel->getVariantById($id);
            $colors = $this->colorModel->getAll();
            $sizes = $this->sizeModel->getAll();
            
            renderView("view/productsvariant/edit.blade.php", 
                compact('variant', 'colors', 'sizes'), 
                "Edit Variant"
            );
        }
    }
}
