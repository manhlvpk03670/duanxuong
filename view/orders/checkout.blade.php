<?php 
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header("Location: /login?redirect=checkout");
    exit;
}

require_once "model/OrderModel.php";
require_once "model/CartModel.php";
require_once "utils/vnpay_helper.php"; // Assuming you will create this helper file later

$orderModel = new OrderModel();
$cartModel = new CartModel();
$userId = $_SESSION['user_id'];

$cartItems = $cartModel->getCartItems($userId);
$totalAmount = 0;
foreach ($cartItems as $item) {
    $totalAmount += $item['total_price'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!empty($cartItems)) {
        // Get user input from POST
        $fullname = $_POST['fullname'];
        $phone = $_POST['phone'];
        $address = $_POST['address'];
        $paymentMethod = $_POST['payment'];

        // Map payment method
        $dbPaymentMethod = ($paymentMethod == 'vnpay') ? 'VNPAY' : (($paymentMethod == 'momo') ? 'BANK_TRANSFER' : 'COD');

        // Handle VNPAY payment
        if ($paymentMethod == 'vnpay') {
            // Store checkout info in session for later
            $_SESSION['checkout_info'] = [
                'fullname' => $fullname,
                'phone' => $phone,
                'address' => $address
            ];

            // Create the order first to get orderId (mark as pending)
            $orderId = $orderModel->createOrder($userId, $totalAmount, $dbPaymentMethod, $fullname, $phone, $address, 'pending');

            if ($orderId) {
                // Save order details
                foreach ($cartItems as $item) {
                    $orderModel->addOrderDetail($orderId, $item['variant_id'], $item['variant_price'], $item['quantity'], $item['total_price']);
                    $orderModel->updateProductVariantQuantity($item['variant_id'], $item['quantity']);
                }

                // Save the orderId for VNPAY redirection
                $_SESSION['vnpay_order_id'] = $orderId;

                // Generate VNPAY URL
                $vnp_Url = "https://sandbox.vnpayment.vn/paymentv2/vpcpay.html";
                $vnp_Returnurl = "http://localhost:8000/orders/vnpay_return";
                $vnp_TmnCode = "A7KX5GQG";
                $vnp_HashSecret = "KEMC7FSFIB1OKEXFZ46VWKD1ZF3DLQJR";
                $vnp_OrderInfo = 'Thanh toán đơn hàng #' . $orderId;
                $vnp_OrderType = 'billpayment';
                $vnp_Amount = $totalAmount * 100;
                $vnp_Locale = 'vn';
                $vnp_IpAddr = $_SERVER['REMOTE_ADDR'];

                // Build the payment URL
                $inputData = array(
                    "vnp_Version" => "2.1.0",
                    "vnp_TmnCode" => $vnp_TmnCode,
                    "vnp_Amount" => $vnp_Amount,
                    "vnp_Command" => "pay",
                    "vnp_CreateDate" => date('YmdHis'),
                    "vnp_CurrCode" => "VND",
                    "vnp_IpAddr" => $vnp_IpAddr,
                    "vnp_Locale" => $vnp_Locale,
                    "vnp_OrderInfo" => $vnp_OrderInfo,
                    "vnp_OrderType" => $vnp_OrderType,
                    "vnp_ReturnUrl" => $vnp_Returnurl,
                    "vnp_TxnRef" => $orderId
                );

                ksort($inputData);
                $query = "";
                $hashdata = "";
                foreach ($inputData as $key => $value) {
                    $hashdata .= '&' . urlencode($key) . "=" . urlencode($value);
                    $query .= urlencode($key) . "=" . urlencode($value) . '&';
                }

                $vnp_Url = $vnp_Url . "?" . $query;
                if (!empty($vnp_HashSecret)) {
                    $vnpSecureHash = hash_hmac('sha512', ltrim($hashdata, '&'), $vnp_HashSecret);
                    $vnp_Url .= 'vnp_SecureHash=' . $vnpSecureHash;
                }

                // Clear the cart before redirecting
                $cartModel->deleteCartAll($userId);

                // Redirect to VNPAY payment page
                header('Location: ' . $vnp_Url);
                exit;
            }
        } else {
            // Process regular payment methods (COD or MOMO)
            $orderId = $orderModel->createOrder($userId, $totalAmount, $dbPaymentMethod, $fullname, $phone, $address, 'completed');

            if ($orderId) {
                // Save order details and update product quantity
                foreach ($cartItems as $item) {
                    $orderModel->addOrderDetail($orderId, $item['variant_id'], $item['variant_price'], $item['quantity'], $item['total_price']);
                    $orderModel->updateProductVariantQuantity($item['variant_id'], $item['quantity']);
                }

                // Clear the cart
                $cartModel->deleteCartAll($userId);

                // Redirect to success page
                header("Location: /orders/success?id=$orderId");
                exit;
            }
        }
    }
}
?>


<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thanh toán</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.1/font/bootstrap-icons.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        :root {
            --primary-color: #4361ee;
            --secondary-color: #3a0ca3;
            --accent-color: #f72585;
            --success-color: #06d6a0;
            --warning-color: #ffd166;
            --danger-color: #ef476f;
            --light-bg: #f8f9fa;
            --dark-text: #212529;
        }
        
        body {
            background-color: var(--light-bg);
            font-family: 'Segoe UI', Roboto, 'Helvetica Neue', sans-serif;
        }
        
        .checkout-container {
            background-color: white;
            border-radius: 15px;
            box-shadow: 0 6px 15px rgba(0,0,0,0.05);
        }
        
        .checkout-header {
            position: relative;
            padding-bottom: 15px;
            margin-bottom: 25px;
            color: var(--secondary-color);
        }
        
        .checkout-header:after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 3px;
            background-color: var(--accent-color);
        }
        
        .order-summary {
            background-color: rgba(67, 97, 238, 0.03);
            border-radius: 12px;
            padding: 20px;
        }
        
        .order-summary-header {
            font-size: 1.3rem;
            font-weight: 600;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px dashed #dee2e6;
            color: var(--secondary-color);
        }
        
        .product-img {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 8px;
        }
        
        .order-item {
            padding: 10px 0;
            border-bottom: 1px solid #eee;
        }
        
        .order-item:last-child {
            border-bottom: none;
        }
        
        .total-amount {
            font-size: 1.5rem;
            color: var(--accent-color);
            font-weight: 700;
        }
        
        .form-label {
            font-weight: 500;
            color: var(--dark-text);
        }
        
        .form-control, .form-select {
            border-radius: 10px;
            padding: 12px 15px;
            border: 1px solid #dee2e6;
            transition: all 0.3s ease;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.25rem rgba(67, 97, 238, 0.15);
        }
        
        .payment-methods {
            margin-top: 20px;
        }
        
        .payment-method-item {
            margin-bottom: 15px;
            border-radius: 10px;
            border: 1px solid #eee;
            transition: all 0.3s ease;
        }
        
        .payment-method-item:hover {
            border-color: var(--primary-color);
        }
        
        .form-check-input:checked ~ .form-check-label {
            color: var(--primary-color);
            font-weight: 600;
        }
        
        .form-check-input:checked ~ .form-check-label .payment-icon {
            color: var(--primary-color);
        }
        
        .payment-icon {
            font-size: 1.5rem;
            margin-right: 10px;
            color: #6c757d;
        }
        
        .order-button {
            background-color: var(--success-color);
            border-color: var(--success-color);
            border-radius: 50px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 12px 30px;
            transition: all 0.3s ease;
        }
        
        .order-button:hover {
            background-color: #05b589;
            border-color: #05b589;
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(6, 214, 160, 0.3);
        }
        
        .back-to-shop {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .back-to-shop:hover {
            color: var(--secondary-color);
            text-decoration: underline;
        }
        
        @media (max-width: 991px) {
            .order-summary {
                margin-bottom: 30px;
            }
        }
    </style>
</head>
<body>
    <div class="container py-5">
        <div class="checkout-container p-4 p-md-5">
            <h2 class="checkout-header text-center mb-4">
                <i class="bi bi-credit-card me-2"></i>Thanh toán đơn hàng
            </h2>

            <?php if (empty($cartItems)) : ?>
                <div class="text-center py-5">
                    <i class="bi bi-cart-x" style="font-size: 3rem; color: #d1d1d1;"></i>
                    <p class="mt-3 fs-5 text-secondary">Giỏ hàng của bạn đang trống.</p>
                    <a href="/shop" class="btn btn-primary mt-3">
                        <i class="bi bi-shop me-2"></i>Quay lại cửa hàng
                    </a>
                </div>
            <?php else : ?>
                <div class="row">
                    <!-- Thông tin đơn hàng -->
                    <div class="col-lg-4 order-lg-2">
                        <div class="order-summary mb-4">
                            <h3 class="order-summary-header">
                                <i class="bi bi-bag-check me-2"></i>Tóm tắt đơn hàng
                            </h3>
                            
                            <?php foreach ($cartItems as $item) : ?>
                                <div class="order-item">
                                    <div class="d-flex align-items-center">
                                        <img src="<?= $item['image_url'] ?>" class="product-img me-3" alt="<?= htmlspecialchars($item['product_name']) ?>">
                                        <div>
                                            <p class="mb-0 fw-medium"><?= htmlspecialchars($item['product_name']) ?></p>
                                            <div class="d-flex text-secondary small">
                                                <span class="me-2">SL: <?= $item['quantity'] ?></span>
                                                <?php if (!empty($item['color_name'])) : ?>
                                                    <span class="me-2">Màu: <?= $item['color_name'] ?></span>
                                                <?php endif; ?>
                                                <?php if (!empty($item['size_name'])) : ?>
                                                    <span>Size: <?= $item['size_name'] ?></span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="d-flex justify-content-between mt-2">
                                        <span class="text-secondary small"><?= number_format($item['variant_price'], 0, ',', '.') ?> đ x <?= $item['quantity'] ?></span>
                                        <span class="fw-medium"><?= number_format($item['total_price'], 0, ',', '.') ?> đ</span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                            
                            <div class="mt-3 pt-3 border-top">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="fw-medium">Tổng cộng:</span>
                                    <span class="total-amount"><?= number_format($totalAmount, 0, ',', '.') ?> đ</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Form thanh toán -->
                    <div class="col-lg-8 order-lg-1">
                        <form method="POST" id="checkout-form">
                            <div class="card border-0 shadow-sm mb-4">
                                <div class="card-body p-4">
                                    <h4 class="mb-3 text-primary">
                                        <i class="bi bi-person-badge me-2"></i>Thông tin giao hàng
                                    </h4>
                                    
                                    <div class="row g-3">
                                        <div class="col-12">
                                            <label for="fullname" class="form-label">Họ và tên</label>
                                            <input type="text" class="form-control" id="fullname" name="fullname" required value="<?= $_SESSION['user_name'] ?? '' ?>">
                                        </div>
                                        
                                        <div class="col-md-6">
                                            <label for="phone" class="form-label">Số điện thoại</label>
                                            <input type="tel" class="form-control" id="phone" name="phone" required value="<?= $_SESSION['user_phone'] ?? '' ?>">
                                        </div>
                                        
                                        <div class="col-md-6">
                                            <label for="email" class="form-label">Email (tùy chọn)</label>
                                            <input type="email" class="form-control" id="email" name="email" value="<?= $_SESSION['user_email'] ?? '' ?>">
                                        </div>
                                        
                                        <div class="col-12">
                                            <label for="address" class="form-label">Địa chỉ giao hàng</label>
                                            <input type="text" class="form-control" id="address" name="address" required value="<?= $_SESSION['user_address'] ?? '' ?>">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="card border-0 shadow-sm mb-4">
                                <div class="card-body p-4">
                                    <h4 class="mb-3 text-primary">
                                        <i class="bi bi-wallet2 me-2"></i>Phương thức thanh toán
                                    </h4>
                                    
                                    <div class="payment-methods">
                                        <div class="payment-method-item p-3">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="payment" id="payment-cod" value="cod" checked>
                                                <label class="form-check-label d-flex align-items-center" for="payment-cod">
                                                    <i class="bi bi-cash-coin payment-icon"></i>
                                                    <div>
                                                        <span class="d-block">Thanh toán khi nhận hàng (COD)</span>
                                                        <small class="text-muted">Thanh toán bằng tiền mặt khi nhận được hàng</small>
                                                    </div>
                                                </label>
                                            </div>
                                        </div>
                                        
                                        <div class="payment-method-item p-3">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="payment" id="payment-vnpay" value="vnpay">
                                                <label class="form-check-label d-flex align-items-center" for="payment-vnpay">
                                                    <i class="bi bi-credit-card payment-icon"></i>
                                                    <div>
                                                        <span class="d-block">Thanh toán VNPAY</span>
                                                        <small class="text-muted">Thanh toán trực tuyến qua cổng VNPAY</small>
                                                    </div>
                                                </label>
                                            </div>
                                        </div>
                                        
                                        <div class="payment-method-item p-3">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="payment" id="payment-momo" value="momo">
                                                <label class="form-check-label d-flex align-items-center" for="payment-momo">
                                                    <i class="bi bi-phone payment-icon"></i>
                                                    <div>
                                                        <span class="d-block">Thanh toán MoMo</span>
                                                        <small class="text-muted">Chuyển khoản qua ví điện tử MoMo</small>
                                                    </div>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="d-grid gap-2 mt-4">
                                <button type="submit" class="btn btn-success order-button">
                                    <i class="bi bi-bag-check me-2"></i>Hoàn tất đặt hàng
                                </button>
                                <a href="/cart" class="back-to-shop text-center mt-3">
                                    <i class="bi bi-arrow-left me-1"></i>Quay lại giỏ hàng
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Bootstrap 5 JavaScript Bundle with Popper -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#checkout-form').on('submit', function(e) {
                if (!confirm('Bạn có chắc chắn muốn đặt hàng?')) {
                    e.preventDefault();
                }
            });
        });
    </script>
</body>
</html>