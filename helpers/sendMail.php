<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../PHPMailer-master/src/PHPMailer.php';
require_once __DIR__ . '/../PHPMailer-master/src/SMTP.php';
require_once __DIR__ . '/../PHPMailer-master/src/Exception.php';
require_once __DIR__ . '/../model/OrderModel.php'; // Import model

class Mailer {
    private $mail;
    private $orderModel;
    private $conn;

    public function __construct() {
        $this->mail = new PHPMailer(true);
        $this->orderModel = new OrderModel();
        $this->conn = $this->orderModel->getConnection(); // ✅ Lấy kết nối từ OrderModel

        // Cấu hình SMTP
        $this->mail->isSMTP();
        $this->mail->Host = 'smtp.gmail.com';
        $this->mail->SMTPAuth = true;
        $this->mail->Username = 'manhlvpk03670@gmail.com';
        $this->mail->Password = 'pevn kfrv kobu mnjc';
        $this->mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $this->mail->Port = 587;
        $this->mail->CharSet = 'UTF-8';
    }

    public function sendOrderConfirmation($orderId) {
        try {
            // Lấy thông tin đơn hàng
            $order = $this->orderModel->getOrderById($orderId);
            if (!$order) {
                throw new Exception("Không tìm thấy đơn hàng #$orderId");
            }

            // Lấy email user từ bảng users
            $query = "SELECT email, name FROM users WHERE id = :user_id";
            $stmt = $this->conn->prepare($query); // ✅ Sử dụng $this->conn thay vì orderModel->conn
            $stmt->bindParam(':user_id', $order['user_id']);
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user) {
                throw new Exception("Không tìm thấy thông tin user");
            }

            $email = $user['email'];
            $name = $user['name'];

            // Lấy chi tiết đơn hàng
            $orderDetails = $this->orderModel->getOrderDetailsByOrderId($orderId);
            $itemsHtml = "";
            foreach ($orderDetails as $item) {
                $itemsHtml .= "<tr>
                    <td>{$item['product_name']}</td>
                    <td>{$item['variant_price']} VND</td>
                    <td>{$item['quantity']}</td>
                    <td>{$item['subtotal']} VND</td>
                </tr>";
            }

            // Tạo nội dung email
            $emailContent = "
                <h2>Xin chào $name,</h2>
                <p>Cảm ơn bạn đã đặt hàng tại cửa hàng của chúng tôi. Dưới đây là thông tin đơn hàng của bạn:</p>
                <table border='1' cellpadding='5' cellspacing='0' width='100%'>
                    <tr>
                        <th>Sản phẩm</th>
                        <th>Giá</th>
                        <th>Số lượng</th>
                        <th>Tổng</th>
                    </tr>
                    $itemsHtml
                </table>
                <p><strong>Tổng tiền:</strong> {$order['total_price']} VND</p>
                <p><strong>Phương thức thanh toán:</strong> {$order['payment_method']}</p>
                <p>Chúng tôi sẽ liên hệ với bạn để xác nhận đơn hàng.</p>
                <p>Trân trọng,<br>Đội ngũ Supersports Vietnam</p>
            ";

            // Gửi email
            $this->mail->setFrom('manhlvpk03670@gmail.com', 'Supersports Vietnam');
            $this->mail->addAddress($email, $name);
            $this->mail->Subject = "Xác nhận đơn hàng #$orderId";
            $this->mail->isHTML(true);
            $this->mail->Body = $emailContent;
            
            $this->mail->send();
            return "Email xác nhận đơn hàng đã được gửi thành công!";
        } catch (Exception $e) {
            return "Lỗi khi gửi email: " . $e->getMessage();
        }
    }
}

// Test gửi email
$mailer = new Mailer();
echo $mailer->sendOrderConfirmation(1); // Thay 1 bằng ID đơn hàng cần gửi
?>
