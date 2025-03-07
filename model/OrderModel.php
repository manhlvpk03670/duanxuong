<?php
require_once "Database.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/../PHPMailer/src/SMTP.php';
require_once __DIR__ . '/../PHPMailer/src/Exception.php';

class OrderModel
{
    private $conn;

    public function __construct()
    {
        $database = new Database();
        $this->conn = $database->getConnection();
    }
    public function getConnection()
    {
        return $this->conn; // ✅ Thêm phương thức này để lấy kết nối PDO
    }
    public function getAllOrders()
    {
        $query = "SELECT orders.*, users.name AS user_name, 
                         (SELECT rating FROM order_reviews WHERE order_reviews.order_id = orders.id LIMIT 1) AS rating 
                  FROM orders 
                  JOIN users ON orders.user_id = users.id 
                  ORDER BY orders.created_at DESC";
    
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function updateProductVariantQuantity($variantId, $quantity) {
        $query = "UPDATE product_variants SET quantity = quantity - :quantity WHERE id = :variantId";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':quantity', $quantity, PDO::PARAM_INT);
        $stmt->bindParam(':variantId', $variantId, PDO::PARAM_INT);
        return $stmt->execute();
    }
    
    public function getOrderById($id)
    {
        $query = "SELECT orders.*, users.email FROM orders 
                  JOIN users ON orders.user_id = users.id
                  WHERE orders.id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $order = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$order) {
            return null; // Trả về null nếu không tìm thấy đơn hàng
        }

        return $order;
    }


    public function getOrdersByUserId($userId)
    {
        $query = "SELECT orders.*, users.name AS user_name, 
                         (SELECT rating FROM order_reviews WHERE order_reviews.order_id = orders.id LIMIT 1) AS rating 
                  FROM orders 
                  JOIN users ON orders.user_id = users.id 
                  WHERE orders.user_id = :user_id 
                  ORDER BY orders.created_at DESC";
    
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    

    public function getOrderDetailsByOrderId($orderId)
    {
        $query = "SELECT od.*, 
                         pv.sku AS variant_sku, pv.price AS variant_price, pv.quantity AS variant_quantity, 
                         c.name AS color, s.name AS size, 
                         p.name AS product_name, p.image_url 
                  FROM order_details od
                  INNER JOIN product_variants pv ON od.product_variant_id = pv.id
                  INNER JOIN products p ON pv.product_id = p.id
                  LEFT JOIN colors c ON pv.colorId = c.id
                  LEFT JOIN sizes s ON pv.sizeId = s.id
                  WHERE od.order_id = :order_id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':order_id', $orderId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    



    public function createOrder($userId, $totalPrice, $paymentMethod)
    {
        try {
            $query = "INSERT INTO orders (user_id, total_price, payment_method, status, created_at, updated_at) 
                      VALUES (:user_id, :total_price, :payment_method, 'pending', NOW(), NOW())";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmt->bindParam(':total_price', $totalPrice, PDO::PARAM_STR);
            $stmt->bindParam(':payment_method', $paymentMethod, PDO::PARAM_STR);

            $stmt->execute();
            $orderId = $this->conn->lastInsertId();

            // Lấy email của user
            $userEmail = $this->getUserEmail($userId);

            // Gửi email xác nhận đơn hàng
            if ($userEmail) {
                $this->sendOrderEmail($userEmail, $orderId, $totalPrice, $paymentMethod);
            }

            return $orderId;
        } catch (PDOException $e) {
            die("Lỗi khi tạo đơn hàng: " . $e->getMessage());
        }
    }

    private function getUserEmail($userId)
    {
        $query = "SELECT email FROM users WHERE id = :user_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchColumn();
    }

    private function sendOrderEmail($userEmail, $orderId, $totalPrice, $paymentMethod)
    {
        $mail = new PHPMailer(true);

        try {
            // Cấu hình SMTP
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'manhlvpk03670@gmail.com';
            $mail->Password   = 'pevn kfrv kobu mnjc';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            // Người gửi & nhận
            $mail->setFrom('manhlvpk03670@gmail.com', 'VM SPORTS');
            $mail->addAddress($userEmail);

            // Lấy danh sách sản phẩm trong đơn hàng
            $orderDetails = $this->getOrderDetailsByOrderId($orderId);
            $orderItemsHtml = "";

            foreach ($orderDetails as $item) {
                $productName = htmlspecialchars($item['product_name']);
                $imageUrl = htmlspecialchars($item['image_url']);
                $price = number_format($item['variant_price'], 0, ',', '.');
                $quantity = htmlspecialchars($item['quantity']);
                $subtotal = number_format($item['variant_price'] * $quantity, 0, ',', '.'); // ✅ Đảm bảo tính đúng tổng từng sản phẩm

                $orderItemsHtml .= "
                <tr>
                    <td style='text-align: center;'><img src='$imageUrl' alt='$productName' style='width: 80px; height: auto; border-radius: 5px;'></td>
                    <td>$productName</td>
                    <td style='text-align: center;'>$quantity</td>
                    <td style='text-align: right;'>$price VNĐ</td>
                    <td style='text-align: right; font-weight: bold;'>$subtotal VNĐ</td>
                </tr>
            ";
            }

            // Nội dung email
            $mail->isHTML(true);
            $mail->Subject = '=?UTF-8?B?' . base64_encode("Xác nhận đơn hàng #$orderId - VM Store") . '?=';
            $mail->Body    = "
            <div style='font-family: Arial, sans-serif; color: #333; max-width: 600px; margin: auto; padding: 20px; border: 1px solid #ddd; border-radius: 10px;'>
                <h2 style='text-align: center; color: #e67e22;'>Cảm ơn bạn đã đặt hàng tại VM store!</h2>
                <p style='text-align: center;'>Đơn hàng <strong>#$orderId</strong> của bạn đã được xác nhận.</p>
                <p><strong>Phương thức thanh toán:</strong> $paymentMethod</p>
                <table style='width: 100%; border-collapse: collapse; margin-top: 15px;'>
                    <tbody>
                    </tbody>
                </table>
                <p style='text-align: right; font-size: 18px; font-weight: bold; margin-top: 15px;'>Tổng cộng: <span style='color: #e67e22;'>" . number_format($totalPrice, 0, ',', '.') . " VNĐ</span></p>
                <p style='text-align: center; margin-top: 20px;'>Chúng tôi sẽ sớm giao hàng đến bạn!</p>
                <p style='text-align: center;'><strong>VM SPORTS</strong></p>
            </div>
        ";

            $mail->send();
            error_log("Email gửi thành công đến: $userEmail");
        } catch (Exception $e) {
            error_log("Không thể gửi email: " . $mail->ErrorInfo);
        }
    }



    public function addOrderDetail($orderId, $productVariantId, $price, $quantity, $subtotal)
    {
        try {
            $query = "INSERT INTO order_details (order_id, product_variant_id, price, quantity, subtotal) 
                      VALUES (:order_id, :product_variant_id, :price, :quantity, :subtotal)";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':order_id', $orderId);
            $stmt->bindParam(':product_variant_id', $productVariantId);
            $stmt->bindParam(':price', $price, PDO::PARAM_STR);
            $stmt->bindParam(':quantity', $quantity, PDO::PARAM_INT);
            $stmt->bindParam(':subtotal', $subtotal, PDO::PARAM_STR);
            $stmt->execute();
            return true;
        } catch (PDOException $e) {
            die("Lỗi khi thêm chi tiết đơn hàng: " . $e->getMessage());
        }
    }


    public function updateOrderStatus($orderId, $status)
    {
        $query = "UPDATE orders SET status = :status WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $orderId, PDO::PARAM_INT);
        $stmt->bindParam(':status', $status, PDO::PARAM_STR);

        if ($stmt->execute()) {
            // Lấy thông tin khách hàng và đơn hàng
            $orderInfo = $this->getOrderById($orderId);
            if ($orderInfo) {
                $userEmail = $orderInfo['email'];
                $totalPrice = $orderInfo['total_price'];
                $paymentMethod = $orderInfo['payment_method'];

                // Gửi email thông báo cập nhật trạng thái đơn hàng
                $this->sendOrderStatusEmail($userEmail, $orderId, $status, $totalPrice, $paymentMethod);
            }
            return true;
        }
        return false;
    }

    public function sendOrderStatusEmail($userEmail, $orderId, $status, $totalPrice, $paymentMethod)
    {
        $mail = new PHPMailer(true);

        try {
            // Cấu hình SMTP
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'manhlvpk03670@gmail.com';
            $mail->Password   = 'pevn kfrv kobu mnjc';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            // Người gửi & nhận
            $mail->setFrom('manhlvpk03670@gmail.com', 'VM SPORTS');
            $mail->addAddress($userEmail);

            // Lấy danh sách sản phẩm trong đơn hàng
            $orderDetails = $this->getOrderDetailsByOrderId($orderId);
            $orderItemsHtml = "";
            $imageCounter = 0;
            $embeddedImages = [];

            // Xác định màu trạng thái
            $statusColor = '#3498db'; // Màu mặc định: xanh dương
            $statusIcon = '📋';

            switch (strtolower($status)) {
                case 'đã xác nhận':
                    $statusColor = '#2ecc71'; // Xanh lá
                    $statusIcon = '✅';
                    break;
                case 'đang xử lý':
                    $statusColor = '#f39c12'; // Cam
                    $statusIcon = '⏳';
                    break;
                case 'đang giao hàng':
                    $statusColor = '#3498db'; // Xanh dương
                    $statusIcon = '🚚';
                    break;
                case 'đã giao hàng':
                    $statusColor = '#2ecc71'; // Xanh lá
                    $statusIcon = '📦';
                    break;
                case 'đã hủy':
                    $statusColor = '#e74c3c'; // Đỏ
                    $statusIcon = '❌';
                    break;
            }

            foreach ($orderDetails as $item) {
                $productName = htmlspecialchars($item['product_name']);
                $price = number_format($item['variant_price'], 0, ',', '.');
                $size = $item['size'];
                $color = $item['color'];
                $quantity = htmlspecialchars($item['quantity']);
                $subtotal = number_format($item['variant_price'] * $quantity, 0, ',', '.');

                // Xử lý hình ảnh
                $imageId = 'product_img_' . $imageCounter;
                $imageCounter++;

                if (!empty($item['image_url'])) {
                    $imagePath = $item['image_url'];
                    // Nếu là đường dẫn tương đối, chuyển thành đường dẫn tuyệt đối trên hệ thống
                    if (strpos($imagePath, 'http') !== 0) {
                        // Giả sử thư mục gốc của dự án là $_SERVER['DOCUMENT_ROOT']
                        if (strpos($imagePath, 'uploads/') === 0) {
                            $imagePath = $_SERVER['DOCUMENT_ROOT'] . '/' . $imagePath;
                        } else {
                            $imagePath = $_SERVER['DOCUMENT_ROOT'] . '/uploads/' . $imagePath;
                        }
                    }

                    // Thêm vào danh sách hình ảnh cần nhúng
                    if (file_exists($imagePath)) {
                        $embeddedImages[] = [
                            'id' => $imageId,
                            'path' => $imagePath
                        ];
                        $imageTag = "<img src='cid:$imageId' alt='$productName' style='width: 80px; height: 80px; object-fit: cover; border-radius: 6px;'>";
                    } else {
                        $imageTag = "<div style='width: 80px; height: 80px; background-color: #f1f1f1; text-align: center; line-height: 80px; border-radius: 6px; color: #888;'>No image</div>";
                    }
                } else {
                    $imageTag = "<div style='width: 80px; height: 80px; background-color: #f1f1f1; text-align: center; line-height: 80px; border-radius: 6px; color: #888;'>No image</div>";
                }

                $orderItemsHtml .= "
                    <tr>
                        <td style='padding: 12px; border-bottom: 1px solid #eee; text-align: center;'>$imageTag</td>
                        <td style='padding: 12px; border-bottom: 1px solid #eee;'>
                            <div style='font-weight: 600;'>$productName</div>
                        </td>
                        <td style='padding: 12px; border-bottom: 1px solid #eee; text-align: center;'>$size</td>
                        <td style='padding: 12px; border-bottom: 1px solid #eee; text-align: center;'>
                            <span style='display: inline-block; width: 15px; height: 15px; background-color: $color; border-radius: 50%; margin-right: 5px; vertical-align: middle;'></span>
                            $color
                        </td>
                        <td style='padding: 12px; border-bottom: 1px solid #eee; text-align: center;'>$quantity</td>
                        <td style='padding: 12px; border-bottom: 1px solid #eee; text-align: right;'>$price VNĐ</td>
                        <td style='padding: 12px; border-bottom: 1px solid #eee; text-align: right; font-weight: bold;'>$subtotal VNĐ</td>
                    </tr>
                ";
            }

            // Nội dung email
            $mail->isHTML(true);
            $mail->Subject = '=?UTF-8?B?' . base64_encode("Cập nhật trạng thái đơn hàng #$orderId - VM Store") . '?=';
            $mail->Body    = "
                <!DOCTYPE html>
                <html>
                <head>
                    <meta charset='UTF-8'>
                    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
                </head>
                <body style='margin: 0; padding: 0; font-family: \"Segoe UI\", Arial, sans-serif; background-color: #f6f9fc; color: #333;'>
                    <div style='max-width: 650px; margin: 0 auto; background-color: #ffffff; border-radius: 10px; overflow: hidden; box-shadow: 0 4px 10px rgba(0,0,0,0.1);'>
                        <!-- Header -->
                        <div style='background-color: #000; padding: 20px; text-align: center;'>
                            <h1 style='margin: 0; color: #fff; font-size: 24px; font-weight: 600;'>VM Store</h1>
                        </div>
                        
                        <!-- Status Banner -->
                        <div style='background-color: $statusColor; color: white; padding: 20px; text-align: center;'>
                            <h2 style='margin: 0; font-size: 26px;'>$statusIcon Đơn hàng #$orderId</h2>
                            <p style='margin: 10px 0 0; font-size: 18px; font-weight: 500;'>Trạng thái: $status</p>
                        </div>
                        
                        <!-- Content -->
                        <div style='padding: 30px 25px;'>
                            <p style='font-size: 16px; line-height: 1.5; margin-top: 0;'>Kính gửi Quý khách,</p>
                            <p style='font-size: 16px; line-height: 1.5;'>Đơn hàng của bạn đã được cập nhật trạng thái thành <strong>$status</strong>.</p>
                            
                            <!-- Order Info -->
                            <div style='background-color: #f8f9fa; border-radius: 8px; padding: 15px; margin: 20px 0;'>
                                <table style='width: 100%; border-collapse: collapse;'>
                                    <tr>
                                        <td style='padding: 8px 0;'><strong>Mã đơn hàng:</strong></td>
                                        <td style='padding: 8px 0; text-align: right;'>#$orderId</td>
                                    </tr>
                                    <tr>
                                        <td style='padding: 8px 0;'><strong>Phương thức thanh toán:</strong></td>
                                        <td style='padding: 8px 0; text-align: right;'>$paymentMethod</td>
                                    </tr>
                                </table>
                            </div>
                            
                            <!-- Products Table -->
                            <h3 style='font-size: 18px; margin-bottom: 15px; border-bottom: 2px solid #eee; padding-bottom: 10px;'>Chi tiết đơn hàng</h3>
                            <table style='width: 100%; border-collapse: collapse;'>
                                <thead>
                                    <tr style='background-color: #f8f9fa;'>
                                        <th style='padding: 12px; text-align: center; border-bottom: 2px solid #eee;'>Hình ảnh</th>
                                        <th style='padding: 12px; text-align: left; border-bottom: 2px solid #eee;'>Sản phẩm</th>
                                        <th style='padding: 12px; text-align: center; border-bottom: 2px solid #eee;'>Size</th>
                                        <th style='padding: 12px; text-align: center; border-bottom: 2px solid #eee;'>Màu</th>
                                        <th style='padding: 12px; text-align: center; border-bottom: 2px solid #eee;'>SL</th>
                                        <th style='padding: 12px; text-align: right; border-bottom: 2px solid #eee;'>Đơn giá</th>
                                        <th style='padding: 12px; text-align: right; border-bottom: 2px solid #eee;'>Thành tiền</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    $orderItemsHtml
                                </tbody>
                            </table>
                            
                            <!-- Total -->
                            <div style='margin-top: 20px; text-align: right;'>
                                <table style='width: 100%; max-width: 350px; margin-left: auto; border-collapse: collapse;'>
                                    <tr>
                                        <td style='padding: 8px 0;'><strong>Tổng cộng:</strong></td>
                                        <td style='padding: 8px 0; text-align: right; font-size: 20px; font-weight: bold; color: $statusColor;'>" . number_format($totalPrice, 0, ',', '.') . " VNĐ</td>
                                    </tr>
                                </table>
                            </div>
                            
                            <div style='margin-top: 30px; text-align: center;'>
                                <p style='font-size: 15px;'>Nếu bạn có bất kỳ câu hỏi nào, vui lòng liên hệ với chúng tôi qua email: <a href='mailto:vanmanhdautrau@gmail.com' style='color: #3498db; text-decoration: none;'>VMstore</a></p>
                            </div>
                        </div>
                        
                        <!-- Footer -->
                        <div style='background-color: #f8f9fa; padding: 20px; text-align: center; border-top: 1px solid #eee;'>
                            <p style='margin: 0; font-size: 14px; color: #666;'>© 2025 VMstore. Tất cả các quyền được bảo lưu.</p>
                            <div style='margin-top: 15px;'>
                                <a href='#' style='display: inline-block; margin: 0 5px; color: #3498db;'>
                                    <span style='font-size: 20px;'>📱</span>
                                </a>
                                <a href='#' style='display: inline-block; margin: 0 5px; color: #3498db;'>
                                    <span style='font-size: 20px;'>👍</span>
                                </a>
                                <a href='#' style='display: inline-block; margin: 0 5px; color: #3498db;'>
                                    <span style='font-size: 20px;'>📷</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </body>
                </html>
            ";

            // Nhúng tất cả hình ảnh vào email
            foreach ($embeddedImages as $image) {
                if (file_exists($image['path'])) {
                    $mail->addEmbeddedImage($image['path'], $image['id']);
                }
            }

            $mail->send();
            error_log("Email cập nhật trạng thái gửi thành công đến: $userEmail");
            return true;
        } catch (Exception $e) {
            error_log("Không thể gửi email: " . $mail->ErrorInfo);
            return false;
        }
    }

    public function deleteOrder($id)
    {
        // Start transaction - because we need to delete from multiple tables
        $this->conn->beginTransaction();

        try {
            // Delete order details first
            $query1 = "DELETE FROM order_details WHERE order_id = :id";
            $stmt1 = $this->conn->prepare($query1);
            $stmt1->bindParam(':id', $id);
            $stmt1->execute();

            // Then delete the order
            $query2 = "DELETE FROM orders WHERE id = :id";
            $stmt2 = $this->conn->prepare($query2);
            $stmt2->bindParam(':id', $id);
            $stmt2->execute();

            // Commit the transaction
            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            // An error occurred, rollback the transaction
            $this->conn->rollBack();
            return false;
        }
    }

    // Đếm số đơn hàng theo trạng thái
    public function getOrderCountByStatus($status)
    {
        $query = "SELECT COUNT(*) FROM orders WHERE status = :status";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':status', $status);
        $stmt->execute();
        return $stmt->fetchColumn();
    }

    // Tính tổng doanh thu từ các đơn hàng đã hoàn thành
    public function getTotalRevenue()
    {
        $query = "SELECT SUM(total_price) FROM orders WHERE status = 'completed'";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchColumn() ?? 0; // Tránh lỗi nếu không có dữ liệu
    }

    // Lấy danh sách đơn hàng theo trạng thái
    public function getOrdersByStatus($status)
    {
        $query = "SELECT * FROM orders WHERE status = :status";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':status', $status);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Đếm tổng số người dùng
    public function getTotalUsers()
    {
        $query = "SELECT COUNT(*) FROM users";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchColumn();
    }

    // Đếm tổng số đơn hàng
    public function getTotalOrders()
    {
        $query = "SELECT COUNT(*) FROM orders";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchColumn();
    }

    // Đếm tổng số sản phẩm
    public function getTotalProducts()
    {
        $query = "SELECT COUNT(*) FROM products";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchColumn();
    }
    public function getOrderReview($orderId) {
        $stmt = $this->conn->prepare("SELECT * FROM order_reviews WHERE order_id = ?");
        $stmt->execute([$orderId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    public function addOrderReview($orderId, $userId, $rating, $comment) {
        $query = "INSERT INTO order_reviews (order_id, user_id, rating, comment, created_at) 
                  VALUES (:order_id, :user_id, :rating, :comment, NOW())";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':order_id', $orderId, PDO::PARAM_INT);
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindParam(':rating', $rating, PDO::PARAM_INT);
        $stmt->bindParam(':comment', $comment, PDO::PARAM_STR);
        return $stmt->execute();
    }
    
}
