<?php
require_once "Database.php";

class ProductModel
{
    private $conn;

    public function __construct()
    {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function getAllProducts()
    {
        // Lấy danh sách sản phẩm cùng với tên danh mục
        $query = "
            SELECT 
                products.*, 
                categories.name AS category_name 
            FROM 
                products 
            LEFT JOIN 
                categories 
            ON 
                products.category_id = categories.id
        ";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    // ProductVariantModel.php
    public function getVariantsByProductId($productId)
    {
        $sql = "SELECT pv.*, c.name as color_name, s.name as size_name 
            FROM product_variants pv
            JOIN colors c ON pv.color_id = c.id
            JOIN sizes s ON pv.size_id = s.id
            WHERE pv.product_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$productId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function getProductById($id)
    {
        // Lấy thông tin sản phẩm theo ID kèm theo tên danh mục
        $query = "
            SELECT 
                products.*, 
                categories.name AS category_name 
            FROM 
                products 
            LEFT JOIN 
                categories 
            ON 
                products.category_id = categories.id
            WHERE 
                products.id = :id
        ";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    public function addToCart($userId, $productId, $attributesId, $quantity)
    {
        // Kiểm tra xem sản phẩm đã có trong giỏ hàng của người dùng chưa
        $query = "SELECT * FROM cart WHERE user_id = :user_id AND product_id = :product_id AND attributes_id = :attributes_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindParam(':product_id', $productId, PDO::PARAM_INT);
        $stmt->bindParam(':attributes_id', $attributesId, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        // Nếu sản phẩm đã có trong giỏ hàng, cập nhật số lượng
        if ($result) {
            $query = "UPDATE cart SET quantity = quantity + :quantity WHERE user_id = :user_id AND product_id = :product_id AND attributes_id = :attributes_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':quantity', $quantity, PDO::PARAM_INT);
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmt->bindParam(':product_id', $productId, PDO::PARAM_INT);
            $stmt->bindParam(':attributes_id', $attributesId, PDO::PARAM_INT);
            $stmt->execute();
        } else {
            // Nếu sản phẩm chưa có trong giỏ hàng, thêm mới
            $query = "INSERT INTO cart (user_id, product_id, attributes_id, quantity) VALUES (:user_id, :product_id, :attributes_id, :quantity)";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmt->bindParam(':product_id', $productId, PDO::PARAM_INT);
            $stmt->bindParam(':attributes_id', $attributesId, PDO::PARAM_INT);
            $stmt->bindParam(':quantity', $quantity, PDO::PARAM_INT);
            $stmt->execute();
        }

        return $stmt->rowCount() > 0;  // Trả về true nếu có thay đổi trong cơ sở dữ liệu
    }

    public function createProduct($name, $description, $price, $category_id, $image_url)
    {
        // Thêm sản phẩm mới với category_id và image_url
        $query = "
            INSERT INTO products (name, description, price, category_id, image_url) 
            VALUES (:name, :description, :price, :category_id, :image_url)
        ";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':price', $price);
        $stmt->bindParam(':category_id', $category_id);
        $stmt->bindParam(':image_url', $image_url);
        return $stmt->execute();
    }

    public function updateProduct($id, $name, $description, $price, $category_id, $image_url)
    {
        // Lấy thông tin sản phẩm cũ để kiểm tra ảnh cũ
        $product = $this->getProductById($id);
        $old_image = $product['image_url'];

        // Cập nhật thông tin sản phẩm
        $query = "
            UPDATE products 
            SET 
                name = :name, 
                description = :description, 
                price = :price, 
                category_id = :category_id, 
                image_url = :image_url
            WHERE 
                id = :id
        ";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':price', $price);
        $stmt->bindParam(':category_id', $category_id);
        $stmt->bindParam(':image_url', $image_url);
        $result = $stmt->execute();

        // Xóa ảnh cũ nếu có ảnh mới được tải lên
        if ($result && $image_url && $old_image && file_exists($old_image)) {
            unlink($old_image);
        }

        return $result;
    }

    public function deleteProduct($id)
    {
        // Lấy thông tin sản phẩm để xóa ảnh trước khi xóa sản phẩm
        $product = $this->getProductById($id);
        $image_url = $product['image_url'];

        // Xóa sản phẩm theo ID
        $query = "DELETE FROM products WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $result = $stmt->execute();

        // Xóa ảnh nếu tồn tại
        if ($result && $image_url && file_exists($image_url)) {
            unlink($image_url);
        }

        return $result;
    }
    // Phương thức lấy biến thể sản phẩm
    public function getProductVariants($productId)
    {
        $sql = "SELECT pv.*, c.name as color, s.name as size 
                FROM product_variants pv
                JOIN colors c ON pv.colorid = c.id
                JOIN sizes s ON pv.sizeid = s.id
                WHERE pv.product_id = ?";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$productId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    //sub image

    public function getSubImages($productId)
    {
        $query = "SELECT * FROM sub_image WHERE product_id = :product_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':product_id', $productId);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC); // Trả về tất cả các ảnh phụ của sản phẩm
    }

    public function createSubImage($productId, $image)
    {
        // Truy vấn để tạo ảnh phụ mới
        $query = "INSERT INTO sub_image (product_id, image) VALUES (:product_id, :image)";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':product_id', $productId);
        $stmt->bindParam(':image', $image);

        return $stmt->execute(); // Trả về true nếu thực hiện thành công
    }
    public function updateSubImage($subImageId, $image)
    {
        // Cập nhật ảnh phụ theo ID
        $query = "UPDATE sub_image SET image = :image WHERE id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $subImageId);
        $stmt->bindParam(':image', $image);

        return $stmt->execute(); // Trả về true nếu cập nhật thành công
    }
    public function deleteSubImage($subImageId)
    {
        // Xóa ảnh phụ theo ID
        $query = "DELETE FROM sub_image WHERE id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $subImageId);

        return $stmt->execute(); // Trả về true nếu xóa thành công
    }
    public function getSubImageById($subImageId)
    {
        // Truy vấn lấy thông tin ảnh phụ theo ID
        $query = "SELECT * FROM sub_image WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $subImageId);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC); // Trả về thông tin ảnh phụ
    }
    //new
    public function getProductsByFilter($search, $categoryId, $sort)
    {
        $sql = "SELECT * FROM products WHERE 1";
    
        // Lọc theo tên sản phẩm
        if ($search) {
            $sql .= " AND name LIKE :search";
        }
    
        // Lọc theo danh mục
        if ($categoryId) {
            $sql .= " AND category_id = :category_id";
        }
    
        // Sắp xếp theo `sort`
        $validSortOptions = ['price_asc', 'price_desc', 'newest', 'oldest'];
        if (in_array($sort, $validSortOptions)) {
            switch ($sort) {
                case 'price_asc':
                    $sql .= " ORDER BY price ASC"; // Giá tăng dần
                    break;
                case 'price_desc':
                    $sql .= " ORDER BY price DESC"; // Giá giảm dần
                    break;
                case 'newest':
                    $sql .= " ORDER BY created_at DESC"; // Mới nhất
                    break;
                case 'oldest':
                    $sql .= " ORDER BY created_at ASC"; // Cũ nhất
                    break;
            }
        } else {
            $sql .= " ORDER BY created_at DESC"; // Mặc định sắp xếp mới nhất
        }
    
        // Chuẩn bị truy vấn
        $stmt = $this->conn->prepare($sql);
    
        // Liên kết tham số tìm kiếm và danh mục nếu có
        if ($search) {
            $stmt->bindValue(':search', '%' . $search . '%');
        }
        if ($categoryId) {
            $stmt->bindValue(':category_id', $categoryId);
        }
    
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getRelatedProducts($categoryId, $currentProductId, $limit = 4) {
        $query = "
            SELECT * FROM products 
            WHERE category_id = :category_id 
            AND id != :current_product_id 
            ORDER BY RAND() 
            LIMIT :limit
        ";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':category_id', $categoryId, PDO::PARAM_INT);
        $stmt->bindParam(':current_product_id', $currentProductId, PDO::PARAM_INT);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
}
