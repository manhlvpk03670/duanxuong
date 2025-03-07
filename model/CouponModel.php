<?php
require_once "Database.php";

class CouponModel {
    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    // Lấy tất cả mã giảm giá
    public function getAllCoupons() {
        $query = "SELECT * FROM coupons ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Lấy mã giảm giá theo ID
    public function getCouponById($id) {
        $query = "SELECT * FROM coupons WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Tạo mã giảm giá mới
    public function createCoupon($code, $discount, $discountType, $expiry) {
        $query = "INSERT INTO coupons (code, discount, discount_type, expiry_date) 
                  VALUES (:code, :discount, :discount_type, :expiry)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":code", $code);
        $stmt->bindParam(":discount", $discount);
        $stmt->bindParam(":discount_type", $discountType);
        $stmt->bindParam(":expiry", $expiry);
        return $stmt->execute();
    }

    // Cập nhật mã giảm giá
    public function updateCoupon($id, $code, $discount, $discountType, $expiry) {
        $query = "UPDATE coupons 
                  SET code = :code, discount = :discount, discount_type = :discount_type, expiry_date = :expiry 
                  WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id, PDO::PARAM_INT);
        $stmt->bindParam(":code", $code);
        $stmt->bindParam(":discount", $discount);
        $stmt->bindParam(":discount_type", $discountType);
        $stmt->bindParam(":expiry", $expiry);
        return $stmt->execute();
    }

    // Xóa mã giảm giá
    public function deleteCoupon($id) {
        $query = "DELETE FROM coupons WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id, PDO::PARAM_INT);
        return $stmt->execute();
    }
 
    public function getCouponByCode($code) {
        $stmt = $this->conn->prepare("SELECT * FROM coupons WHERE code = ?");
        $stmt->execute([$code]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
}
?>
