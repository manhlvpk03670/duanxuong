<?php
require_once "Database.php";

class CartModel
{
    private $conn;

    public function __construct()
    {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    // Lấy danh sách sản phẩm trong giỏ hàng
    public function getCartItems($userId)
    {
        $sql = "SELECT 
                    pv.id AS variant_id,
                    c.id AS cart_id,
                    c.quantity,
                    p.name AS product_name,
                    p.image_url,
                    pv.price AS variant_price,
                    (pv.price * c.quantity) AS total_price,
                    pv.sku,
                    pv.colorId,
                    pv.sizeId,
                    colors.name AS color_name,
                    sizes.name AS size_name
                FROM cart c
                JOIN product_variants pv ON c.product_variant_id = pv.id
                JOIN products p ON pv.product_id = p.id
                LEFT JOIN colors ON pv.colorId = colors.id
                LEFT JOIN sizes ON pv.sizeId = sizes.id
                WHERE c.user_id = ?";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Thêm sản phẩm vào giỏ hàng
    public function addToCart($userId, $productVariantId, $quantity)
    {
        try {
            // Bắt đầu transaction
            $this->conn->beginTransaction();

            // Kiểm tra sản phẩm có trong giỏ hàng chưa
            $checkQuery = "SELECT id, quantity FROM cart WHERE user_id = :user_id AND product_variant_id = :product_variant_id";
            $stmt = $this->conn->prepare($checkQuery);
            $stmt->bindParam(':user_id', $userId);
            $stmt->bindParam(':product_variant_id', $productVariantId);
            $stmt->execute();
            $existingItem = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($existingItem) {
                // Nếu đã có trong giỏ hàng, cập nhật số lượng
                $newQuantity = $existingItem['quantity'] + $quantity;
                $updateQuery = "UPDATE cart SET quantity = :quantity WHERE id = :cart_id";
                $updateStmt = $this->conn->prepare($updateQuery);
                $updateStmt->bindParam(':quantity', $newQuantity);
                $updateStmt->bindParam(':cart_id', $existingItem['id']);
                $updateStmt->execute();
            } else {
                // Nếu chưa có, thêm mới vào giỏ hàng
                $insertQuery = "INSERT INTO cart (user_id, product_variant_id, quantity) VALUES (:user_id, :product_variant_id, :quantity)";
                $insertStmt = $this->conn->prepare($insertQuery);
                $insertStmt->bindParam(':user_id', $userId);
                $insertStmt->bindParam(':product_variant_id', $productVariantId);
                $insertStmt->bindParam(':quantity', $quantity);
                $insertStmt->execute();
            }

            // Cập nhật số lượng tồn kho
            $updateStockQuery = "UPDATE product_variants SET quantity = quantity - :quantity WHERE id = :product_variant_id";
            $updateStockStmt = $this->conn->prepare($updateStockQuery);
            $updateStockStmt->bindParam(':quantity', $quantity);
            $updateStockStmt->bindParam(':product_variant_id', $productVariantId);
            $updateStockStmt->execute();

            // Commit transaction
            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            // Nếu có lỗi, rollback và trả về false
            $this->conn->rollBack();
            return false;
        }
    }




    // Lấy tổng tiền của giỏ hàng
    public function getCartTotal($userId)
    {
        $sql = "SELECT SUM(pv.price * c.quantity) AS total_amount 
                FROM cart c
                JOIN product_variants pv ON c.product_variant_id = pv.id
                WHERE c.user_id = :user_id";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result['total_amount'] ?? 0;
    }

    public function deleteCart($cartId)
    {
        try {
            $this->conn->beginTransaction();

            // Lấy thông tin sản phẩm từ giỏ hàng
            $sql = "SELECT product_variant_id, quantity FROM cart WHERE id = :cart_id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':cart_id', $cartId);
            $stmt->execute();
            $cartItem = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$cartItem) {
                throw new Exception("Sản phẩm không tồn tại trong giỏ hàng.");
            }

            $productVariantId = $cartItem['product_variant_id'];
            $quantity = $cartItem['quantity'];

            // Xóa sản phẩm khỏi giỏ hàng
            $deleteQuery = "DELETE FROM cart WHERE id = :cart_id";
            $deleteStmt = $this->conn->prepare($deleteQuery);
            $deleteStmt->bindParam(':cart_id', $cartId);
            $deleteStmt->execute();

            // Cập nhật lại số lượng trong product_variants
            $updateStockQuery = "UPDATE product_variants SET quantity = quantity + :quantity WHERE id = :product_variant_id";
            $updateStockStmt = $this->conn->prepare($updateStockQuery);
            $updateStockStmt->bindParam(':quantity', $quantity);
            $updateStockStmt->bindParam(':product_variant_id', $productVariantId);
            $updateStockStmt->execute();

            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollBack();
            return false;
        }
    }


    public function updateCart($cartId, $newQuantity)
    {
        try {
            $this->conn->beginTransaction();

            // Lấy thông tin cũ từ giỏ hàng
            $sql = "SELECT product_variant_id, quantity FROM cart WHERE id = :cart_id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':cart_id', $cartId);
            $stmt->execute();
            $cartItem = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$cartItem) {
                throw new Exception("Sản phẩm không tồn tại trong giỏ hàng.");
            }

            $productVariantId = $cartItem['product_variant_id'];
            $oldQuantity = $cartItem['quantity'];
            $quantityChange = $newQuantity - $oldQuantity;

            // Kiểm tra nếu số lượng thay đổi có hợp lệ không
            if ($quantityChange > 0) {
                // Giảm số lượng tồn kho nếu tăng số lượng trong giỏ hàng
                $stockCheckQuery = "SELECT quantity FROM product_variants WHERE id = :product_variant_id";
                $stockCheckStmt = $this->conn->prepare($stockCheckQuery);
                $stockCheckStmt->bindParam(':product_variant_id', $productVariantId);
                $stockCheckStmt->execute();
                $stock = $stockCheckStmt->fetchColumn();

                if ($stock < $quantityChange) {
                    throw new Exception("Số lượng tồn kho không đủ.");
                }

                $updateStockQuery = "UPDATE product_variants SET quantity = quantity - :quantity_change WHERE id = :product_variant_id";
            } elseif ($quantityChange < 0) {
                // Tăng số lượng tồn kho nếu giảm số lượng trong giỏ hàng
                $quantityChange = abs($quantityChange); // Chuyển về số dương
                $updateStockQuery = "UPDATE product_variants SET quantity = quantity + :quantity_change WHERE id = :product_variant_id";
            }


            $updateStockStmt = $this->conn->prepare($updateStockQuery);
            $updateStockStmt->bindParam(':quantity_change', $quantityChange);
            $updateStockStmt->bindParam(':product_variant_id', $productVariantId);
            $updateStockStmt->execute();

            // Cập nhật số lượng trong giỏ hàng
            $updateCartQuery = "UPDATE cart SET quantity = :new_quantity WHERE id = :cart_id";
            $updateCartStmt = $this->conn->prepare($updateCartQuery);
            $updateCartStmt->bindParam(':new_quantity', $newQuantity);
            $updateCartStmt->bindParam(':cart_id', $cartId);
            $updateCartStmt->execute();

            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollBack();
            return false;
        }
    }
    public function deleteCartAll($userId)
    {
        try {
            $this->conn->beginTransaction();
    
            // Lấy danh sách tất cả sản phẩm trong giỏ hàng của người dùng
            $sql = "SELECT product_variant_id, quantity FROM cart WHERE user_id = :user_id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmt->execute();
            $cartItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
            if (!$cartItems) {
                throw new Exception("Không có sản phẩm nào trong giỏ hàng.");
            }
    
            // Cập nhật lại số lượng sản phẩm trong bảng product_variants
            foreach ($cartItems as $item) {
                $updateStockQuery = "UPDATE product_variants SET quantity = quantity + :quantity WHERE id = :product_variant_id";
                $updateStockStmt = $this->conn->prepare($updateStockQuery);
                $updateStockStmt->bindParam(':quantity', $item['quantity'], PDO::PARAM_INT);
                $updateStockStmt->bindParam(':product_variant_id', $item['product_variant_id'], PDO::PARAM_INT);
                $updateStockStmt->execute();
            }
    
            // Xóa toàn bộ sản phẩm khỏi giỏ hàng
            $deleteQuery = "DELETE FROM cart WHERE user_id = :user_id";
            $deleteStmt = $this->conn->prepare($deleteQuery);
            $deleteStmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $deleteStmt->execute();
    
            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollBack();
            return false;
        }
    }
    
    public function getProductVariantStock($productVariantId)
    {
        $sql = "SELECT quantity FROM product_variants WHERE id = :product_variant_id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':product_variant_id', $productVariantId, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result ? (int)$result['quantity'] : false;
    }
}
