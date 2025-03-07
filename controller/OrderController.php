<?php
require_once "model/OrderModel.php";
require_once "model/UserModel.php";
require_once "view/helpers.php";

class OrderController {
    private $orderModel;
    private $userModel;

    public function __construct() {
        $this->orderModel = new OrderModel();
        $this->userModel = new UserModel();
    }

    // Bổ sung phương thức lấy tất cả người dùng (do không có sẵn trong UserModel)
    private function getAllUsers() {
        $query = "SELECT id, name, email FROM users";
        $database = new Database();
        $conn = $database->getConnection();
        $stmt = $conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function index() {
        $orders = $this->orderModel->getAllOrders();
        // Lấy thêm thông tin người dùng cho mỗi đơn hàng
        foreach ($orders as &$order) {
            $user = $this->userModel->getUserById($order['user_id']);
            $order['user_name'] = $user ? $user['name'] : 'Không xác định';
        }
        renderView("view/orders/order_list.blade.php", compact('orders'), "Danh sách đơn hàng");
    }

    public function show($id) {
        $order = $this->orderModel->getOrderById($id);
        if (!$order) {
            // Xử lý khi không tìm thấy đơn hàng
            header("Location: /orders");
            return;
        }
        
        $orderDetails = $this->orderModel->getOrderDetailsByOrderId($id);
        $user = $this->userModel->getUserById($order['user_id']);
        $review = $this->orderModel->getOrderReview($id); // Lấy đánh giá

        $data = [
            'order' => $order,
            'orderDetails' => $orderDetails,
            'user' => $user,
            'review' => $review

        ];
        
        renderView("view/orders/order_detail.blade.php", $data, "Chi tiết đơn hàng");
    }

    public function create() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $userId = $_POST['user_id'];
            $totalPrice = $_POST['total_price'];
            $paymentMethod = $_POST['payment_method'];
            
            // Tạo đơn hàng mới
            $orderId = $this->orderModel->createOrder($userId, $totalPrice, $paymentMethod);
            
            // Xử lý các chi tiết đơn hàng (giả sử dữ liệu được gửi dưới dạng mảng)
            if (isset($_POST['product_variant_id']) && is_array($_POST['product_variant_id'])) {
                foreach ($_POST['product_variant_id'] as $key => $variantId) {
                    $price = $_POST['price'][$key];
                    $quantity = $_POST['quantity'][$key];
                    $subtotal = $price * $quantity;
                    
                    $this->orderModel->addOrderDetail($orderId, $variantId, $price, $quantity, $subtotal);
                }
            }
            
            header("Location: /orders");
        } else {
            // Lấy danh sách người dùng cho dropdown
            $users = $this->getAllUsers();
            // Có thể lấy thêm danh sách sản phẩm nếu cần
            
            renderView("view/orders/order_create.blade.php", compact('users'), "Tạo đơn hàng mới");
        }
    }

    public function edit($id) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $status = $_POST['status'];
    
            // Lấy thông tin đơn hàng trước khi cập nhật
            $order = $this->orderModel->getOrderById($id);
            if (!$order) {
                $_SESSION['error'] = "Đơn hàng không tồn tại!";
                header("Location: /orders");
                return;
            }
    
            // Cập nhật trạng thái đơn hàng
            $updated = $this->orderModel->updateOrderStatus($id, $status);
    
            if ($updated) {
                // Gửi email thông báo cho khách hàng
                $this->orderModel->sendOrderStatusEmail($order['email'], $id, $status, $order['total_price'], $order['payment_method']);
    
                $_SESSION['success'] = "Cập nhật trạng thái đơn hàng thành công!";
                header("Location: /orders");
            } else {
                $_SESSION['error'] = "Cập nhật trạng thái đơn hàng thất bại!";
                header("Location: /orders/edit/$id");
            }
        } else {
            // Hiển thị trang chỉnh sửa đơn hàng
            $order = $this->orderModel->getOrderById($id);
            if (!$order) {
                $_SESSION['error'] = "Đơn hàng không tồn tại!";
                header("Location: /orders");
                return;
            }
    
            $orderDetails = $this->orderModel->getOrderDetailsByOrderId($id);
            $user = $this->userModel->getUserById($order['user_id']);
    
            $data = [
                'order' => $order,
                'orderDetails' => $orderDetails,
                'user' => $user
            ];
    
            renderView("view/orders/order_edit.blade.php", $data, "Cập nhật đơn hàng");
        }
    }
    

    public function delete($id) {
        $this->orderModel->deleteOrder($id);
        header("Location: /orders");
    }

    public function deleteOrderUser($orderId) {
        // Lấy thông tin đơn hàng trước khi xóa
        $order = $this->orderModel->getOrderById($orderId);
    
        if (!$order) {
            header("Location: /orders");
            exit;
        }
    
        // Lấy user_id từ đơn hàng
        $userId = $order['user_id'];
    
        // Xóa đơn hàng
        $this->orderModel->deleteOrder($orderId);
    
        // Chuyển hướng về danh sách đơn hàng của user đó
        header("Location: /orders/user/$userId");
        exit;
    }
    public function cancelOrderUser($orderId) {
        // Lấy ID người dùng từ session (hoặc nguồn khác)
        $userId = $_SESSION['user_id'] ?? 0;
    
        // Kiểm tra đơn hàng có tồn tại không
        $order = $this->orderModel->getOrderById($orderId);
        if (!$order) {
            $_SESSION['error'] = "Đơn hàng không tồn tại!";
            header("Location: http://localhost:8000/orders/user/$userId");
            exit;
        }
    
        // Cập nhật trạng thái đơn hàng thành 'canceled'
        $this->orderModel->updateOrderStatus($orderId, 'canceled');
    
        $_SESSION['success'] = "Đơn hàng đã được hủy!";
        header("Location: http://localhost:8000/orders/user/$userId");
        exit;
    }
    
    
    
    public function userOrders($userId) {
        $user = $this->userModel->getUserById($userId);
        if (!$user) {
            header("Location: /orders");
            return;
        }
        
        $orders = $this->orderModel->getOrdersByUserId($userId);
        
        renderView("view/orders/user_orders.blade.php", compact('orders', 'user'), "Đơn hàng của người dùng");
    }
    
    public function dashboard() {
        // Lấy dữ liệu thống kê từ model
        $pendingCount = $this->orderModel->getOrderCountByStatus('pending');
        $processingCount = $this->orderModel->getOrderCountByStatus('processing');
        $completedCount = $this->orderModel->getOrderCountByStatus('completed');
        $canceledCount = $this->orderModel->getOrderCountByStatus('canceled');
        $totalRevenue = $this->orderModel->getTotalRevenue();
        $totalUsers = $this->orderModel->getTotalUsers();
        $totalOrders = $this->orderModel->getTotalOrders();
        $totalProducts = $this->orderModel->getTotalProducts();

        // Truyền dữ liệu vào view
        $data = [
            'pendingCount' => $pendingCount,
            'processingCount' => $processingCount,
            'completedCount' => $completedCount,
            'canceledCount' => $canceledCount,
            'totalRevenue' => $totalRevenue,
            'totalUsers' => $totalUsers,
            'totalOrders' => $totalOrders,
            'totalProducts' => $totalProducts
        ];

        renderView("view/orders/order_dashboard.blade.php", $data, "Thống kê đơn hàng");
    }
    
    public function success() {
        if (!isset($_GET['id'])) {
            header("Location: /");
            exit;
        }

        $orderId = $_GET['id'];
        $order = $this->orderModel->getOrderById($orderId);

        if (!$order) {
            header("Location: /");
            exit;
        }

        // Kiểm tra nếu đơn hàng thanh toán qua VNPAY mà chưa hoàn tất thì chuyển hướng đến thất bại
        if ($order['payment_method'] === 'vnpay' && $order['status'] !== 'completed') {
            header("Location: /orders/failed?id=$orderId&code=99");
            exit;
        }

        require_once "view/orders/order_success.blade.php";
    }

    public function vnpayReturn() {
        require_once "utils/vnpay_helper.php";
    
        $vnp_HashSecret = "KEMC7FSFIB1OKEXFZ46VWKD1ZF3DLQJR";
        $vnpayData = $_GET;
    
        if (isset($vnpayData['vnp_ResponseCode']) && isset($vnpayData['vnp_TxnRef'])) {
            $isValidSignature = vnpay_verify_response($vnpayData, $vnp_HashSecret);
    
            if ($isValidSignature) {
                $vnp_ResponseCode = $vnpayData['vnp_ResponseCode'];
                $orderId = intval($vnpayData['vnp_TxnRef']);
    
                // Lấy đơn hàng từ database
                $order = $this->orderModel->getOrderById($orderId);
    
                if ($order) {
                    // Update trạng thái đơn hàng trong DB dựa vào mã phản hồi
                    if ($vnp_ResponseCode == '00') {
                        // Cập nhật trạng thái thành công
                        $this->orderModel->updateOrderStatus($orderId, 'completed');
                        header("Location: /orders/success?id=$orderId");
                    } else {
                        // Cập nhật trạng thái thất bại
                        $this->orderModel->updateOrderStatus($orderId, 'failed');
                        header("Location: /orders/failed?id=$orderId&code=$vnp_ResponseCode");
                    }
                    exit;
                } else {
                    echo "Không tìm thấy đơn hàng.";
                    exit;
                }
            } else {
                echo "Chữ ký không hợp lệ.";
                exit;
            }
        } else {
            header("Location: /");
            exit;
        }
    }
    public function failed() {
        $orderId = $_GET['id'] ?? null;
        $errorCode = $_GET['code'] ?? null;
    
        renderView("view/orders/order_failed.blade.php", compact('orderId', 'errorCode'), "Thanh toán thất bại");
    }
    public function addReview() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $orderId = $_POST['order_id'];
            $userId = $_SESSION['user_id']; // Lấy ID người dùng từ session
            $rating = $_POST['rating'];
            $comment = $_POST['comment'];
    
            // Kiểm tra xem đơn hàng có tồn tại không
            $order = $this->orderModel->getOrderById($orderId);
            if (!$order) {
                $_SESSION['error'] = "Đơn hàng không tồn tại!";
                header("Location: /orders");
                exit;
            }
    
            // Kiểm tra xem người dùng đã đánh giá chưa
            $existingReview = $this->orderModel->getOrderReview($orderId);
            if ($existingReview) {
                $_SESSION['error'] = "Bạn đã đánh giá đơn hàng này rồi!";
                header("Location: /orders/$orderId");
                exit;
            }
    
            // Thêm đánh giá vào database
            $this->orderModel->addOrderReview($orderId, $userId, $rating, $comment);
    
            $_SESSION['success'] = "Đánh giá đơn hàng thành công!";
            header("Location: /orders/view/$orderId");
            exit;
        }
    }
    

}