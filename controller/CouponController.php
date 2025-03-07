<?php
require_once "model/CouponModel.php";
require_once "view/helpers.php";

class CouponController {
    private $couponModel;

    public function __construct() {
        $this->couponModel = new CouponModel();
    }

    // Hiển thị danh sách coupon
    public function index() {
        $coupons = $this->couponModel->getAllCoupons();
        renderView("view/coupon/list.blade.php", compact('coupons'), "Danh sách Coupon");
    }

    // Hiển thị chi tiết coupon
    public function show($id) {
        $coupon = $this->couponModel->getCouponById($id);
        renderView("view/coupon/detail.blade.php", compact('coupon'), "Chi tiết Coupon");
    }

    // Tạo mới coupon
    public function create() {
        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            $code = $_POST["code"] ?? null;
            $discount = $_POST["discount"] ?? null;
            $discountType = $_POST["discount_type"] ?? null;
            $expiry = $_POST["expiry_date"] ?? null;
    
            if ($code && $discount !== null && $discountType && $expiry) {
                $this->couponModel->createCoupon($code, $discount, $discountType, $expiry);
                header("Location: /coupons");
                exit;
            } else {
                echo "Dữ liệu nhập vào không hợp lệ!";
            }
        } else {
            renderView("view/coupon/create.blade.php", [], "Thêm Coupon");
        }
    }
    

    

    // Cập nhật coupon
    public function edit($id) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $code = $_POST['code'] ?? null;
            $discount = $_POST['discount'] ?? null;
            $discountType = $_POST["discount_type"] ?? null;
            $expiry = $_POST['expiry_date'] ?? null;

            if ($code && $discount !== null && $discountType && $expiry) {
                if ($this->couponModel->updateCoupon($id, $code, $discount, $discountType, $expiry)) {
                    header("Location: /coupons");
                    exit;
                }
            } else {
                echo "Dữ liệu nhập vào không hợp lệ!";
            }
        } else {
            $coupon = $this->couponModel->getCouponById($id);
            renderView("view/coupon/edit.blade.php", compact('coupon'), "Chỉnh sửa Coupon");
        }
    }

    // Xóa coupon
    public function delete($id) {
        if ($this->couponModel->deleteCoupon($id)) {
            header("Location: /coupons");
            exit;
        }
    }
    public function applyCoupon() {
        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            $code = $_POST["code"] ?? null;
            if (!$code) {
                echo json_encode(["success" => false, "message" => "Vui lòng nhập mã giảm giá!"]);
                exit;
            }
    
            $coupon = $this->couponModel->getCouponByCode($code);
            if (!$coupon) {
                echo json_encode(["success" => false, "message" => "Mã giảm giá không hợp lệ!"]);
                exit;
            }
    
            if (strtotime($coupon["expiry_date"]) < time()) {
                echo json_encode(["success" => false, "message" => "Mã giảm giá đã hết hạn!"]);
                exit;
            }
    
            $totalAmount = $_SESSION['cart_total'] ?? 0;
            $discountedTotal = ($coupon["discount_type"] === "percent") 
                ? $totalAmount * (1 - ($coupon["discount"] / 100))
                : max($totalAmount - $coupon["discount"], 0);
    
            $_SESSION['cart_discounted_total'] = $discountedTotal;
    
            echo json_encode([
                "success" => true,
                "message" => "Áp dụng mã giảm giá thành công!",
                "discountedTotal" => $discountedTotal
            ]);
            exit;
        }
    }
    
}
