<?php
require_once 'model/CartModel.php';
require_once 'view/helpers.php';

class CartController {
    private $cartModel;

    public function __construct() {
        $this->cartModel = new CartModel();
    }

    // Hiển thị giỏ hàng của người dùng
    public function index() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['user_id'])) {
            header("Location: /login");
            exit();
        }

        $userId = $_SESSION['user_id'];
        $cartItems = $this->cartModel->getCartItems($userId);
        $totalAmount = $this->cartModel->getCartTotal($userId);

        renderView("view/cart.blade.php", compact('cartItems', 'totalAmount'), "Giỏ hàng của bạn");
    }
    public function checkout() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    
        if (!isset($_SESSION['user_id'])) {
            header("Location: /login");
            exit();
        }
    
        $userId = $_SESSION['user_id'];  // Lấy user_id từ session
        $cartItems = $this->cartModel->getCartItems($userId);
        $totalAmount = $this->cartModel->getCartTotal($userId);
    
        if ($cartItems && $totalAmount > 0) {
            $orderModel = new OrderModel();  // Khởi tạo OrderModel
            $cartModel = new CartModel();    // Khởi tạo CartModel
    
            // Truyền thêm $cartModel vào renderView
            renderView("view/orders/checkout.blade.php", compact('cartItems', 'totalAmount', 'orderModel', 'userId', 'cartModel'), "Thanh toán");
        } else {
            echo "Giỏ hàng của bạn trống.";
        }
    }
    
    

    // Thêm sản phẩm vào giỏ hàng
// Thêm sản phẩm vào giỏ hàng
public function addtocart() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (!isset($_SESSION['user_id'])) {
        echo json_encode(["status" => "error", "message" => "Bạn cần đăng nhập để thêm vào giỏ hàng."]);
        return;
    }

    $userId = $_SESSION['user_id'];
    $productVariantId = $_POST['product_variant_id'] ?? null;
    $quantity = $_POST['quantity'] ?? 1;

    if (!$productVariantId || $quantity < 1) {
        echo json_encode(["status" => "error", "message" => "Dữ liệu không hợp lệ."]);
        return;
    }

    // Kiểm tra số lượng tồn kho
    $stock = $this->cartModel->getProductVariantStock($productVariantId);

    if ($stock === false) {
        echo json_encode(["status" => "error", "message" => "Sản phẩm không tồn tại."]);
        return;
    }

    if ($quantity > $stock) {
        echo json_encode(["status" => "error", "message" => "Số lượng sản phẩm không đủ. Hiện chỉ còn $stock sản phẩm."]);
        return;
    }

    // Thêm vào giỏ hàng
    $result = $this->cartModel->addToCart($userId, $productVariantId, $quantity);

    echo json_encode(["status" => $result ? "success" : "error", "message" => $result ? "Sản phẩm đã được thêm vào giỏ hàng." : "Lỗi khi thêm sản phẩm."]);
}


public function deleteCart($id) {
    if ($this->cartModel->deleteCart($id)) {
        $_SESSION['message'] = "Sản phẩm đã được xóa khỏi giỏ hàng.";
    } else {
        $_SESSION['error'] = "Lỗi khi xóa sản phẩm.";
    }
    header("Location: /cart");
    exit();
}


public function updateCart() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $id = $_POST['id'];
        $quantity = $_POST['quantity'];

        if ($this->cartModel->updateCart($id, $quantity)) {
            $_SESSION['message'] = "Cập nhật số lượng thành công.";
        } else {
            $_SESSION['error'] = "Lỗi khi cập nhật số lượng.";
        }

        header("Location: /cart");
        exit();
    }
}

    
    public function deletecartAll() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    
        if (!isset($_SESSION['user_id'])) {
            header("Location: /login");
            exit();
        }
    
        $userId = $_SESSION['user_id'];
        $this->cartModel->deletecartAll($userId); // Gửi user_id vào model
    
        header("Location: /cart");
        exit();
    }
    
}
