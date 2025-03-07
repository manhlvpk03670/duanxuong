<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Giỏ hàng</title>
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
        
        .cart-container {
            background-color: white;
            border-radius: 15px;
            box-shadow: 0 6px 15px rgba(0,0,0,0.05);
        }
        
        .cart-header {
            position: relative;
            padding-bottom: 15px;
            margin-bottom: 25px;
            color: var(--secondary-color);
        }
        
        .cart-header:after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 60px;
            height: 3px;
            background-color: var(--accent-color);
        }
        
        .product-img {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 10px;
        }
        
        .quantity-control {
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .update-quantity {
            width: 65px;
            text-align: center;
            border: 1px solid #ced4da;
            border-radius: 8px;
        }
        
        .cart-actions .btn {
            border-radius: 50px;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 10px 25px;
            transition: all 0.3s ease;
        }
        
        .btn-checkout {
            background-color: var(--success-color);
            border-color: var(--success-color);
        }
        
        .btn-checkout:hover {
            background-color: #05b589;
            border-color: #05b589;
            transform: translateY(-2px);
        }
        
        .btn-danger {
            background-color: var(--danger-color);
            border-color: var(--danger-color);
        }
        
        .btn-danger:hover {
            background-color: #d63b60;
            border-color: #d63b60;
        }
        
        .total-amount {
            font-size: 1.5rem;
            color: var(--accent-color);
            font-weight: 700;
        }
        
        .cart-item {
            transition: all 0.3s ease;
        }
        
        .cart-item:hover {
            background-color: rgba(67, 97, 238, 0.05);
        }
        
        .delete-item {
            width: 36px;
            height: 36px;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: var(--danger-color);
            color: white;
            border-radius: 50%;
            transition: all 0.3s ease;
        }
        
        .delete-item:hover {
            background-color: #d63b60;
            transform: scale(1.1);
        }
        
        .loading {
            display: none;
            color: var(--warning-color);
        }
        
        .table-responsive {
            border-radius: 10px;
            overflow: hidden;
        }
        
        .table {
            margin-bottom: 0;
        }
        
        .table th {
            font-weight: 600;
            color: var(--dark-text);
            background-color: rgba(67, 97, 238, 0.08);
            border-bottom: none;
        }
        
        .empty-cart {
            text-align: center;
            padding: 50px 0;
        }
        
        .empty-cart i {
            font-size: 4rem;
            color: #d1d1d1;
            margin-bottom: 15px;
        }
        
        .empty-cart p {
            font-size: 1.2rem;
            color: #6c757d;
        }
        
        .go-shopping {
            margin-top: 20px;
            display: inline-block;
        }
        
        @media (max-width: 767px) {
            .product-img {
                width: 60px;
                height: 60px;
            }
            
            .quantity-control {
                flex-direction: column;
            }
            
            .cart-actions {
                flex-direction: column;
                gap: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="container py-5">
        <div class="cart-container p-4 p-md-5">
            <h2 class="cart-header mb-4">
                <i class="bi bi-cart3 me-2"></i>Giỏ hàng của bạn
            </h2>

            <?php if (empty($cartItems)) : ?>
                <div class="empty-cart">
                    <i class="bi bi-cart-x"></i>
                    <p>Giỏ hàng của bạn đang trống</p>

                </div>
            <?php else : ?>
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead>
                            <tr>
                                <th scope="col">Sản phẩm</th>
                                <th scope="col">Giá</th>
                                <th scope="col">Màu</th>
                                <th scope="col">Kích thước</th>
                                <th scope="col">Số lượng</th>
                                <th scope="col">Thành tiền</th>
                                <th scope="col"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $totalAmount = 0;
                            $totalQuantity = 0;
                            foreach ($cartItems as $item) :
                                $totalAmount += $item['total_price'];
                                $totalQuantity += $item['quantity'];
                            ?>
                                <tr id="cart-item-<?= $item['cart_id'] ?>" class="cart-item">
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <img src="<?= $item['image_url'] ?>" class="product-img me-3" alt="<?= htmlspecialchars($item['product_name']) ?>">
                                            <span class="product-name"><?= htmlspecialchars($item['product_name']) ?></span>
                                        </div>
                                    </td>
                                    <td><?= number_format($item['variant_price'], 0, ',', '.') ?> đ</td>
                                    <td><?= $item['color_name'] ?? 'N/A' ?></td>
                                    <td><?= $item['size_name'] ?? 'N/A' ?></td>
                                    <td>
                                        <div class="quantity-control">
                                            <input type="number" min="1" value="<?= $item['quantity'] ?>"
                                                data-cart-id="<?= $item['cart_id'] ?>" class="form-control update-quantity">
                                            <span class="loading ms-2"><i class="bi bi-hourglass-split"></i></span>
                                        </div>
                                    </td>
                                    <td class="fw-bold total-price"><?= number_format($item['total_price'], 0, ',', '.') ?> đ</td>
                                    <td>
                                        <a href="/cart/delete/<?= $item['cart_id'] ?>" class="delete-item" 
                                           data-cart-id="<?= $item['cart_id'] ?>" title="Xóa sản phẩm">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div class="row mt-4">
                    <div class="col-md-6">
                        <button id="clear-cart" class="btn btn-outline-danger">
                            <i class="bi bi-trash me-2"></i>Xóa toàn bộ giỏ hàng
                        </button>
                    </div>
                    <div class="col-md-6 text-md-end">
                        <div class="d-flex flex-column flex-md-row justify-content-md-end align-items-md-center gap-3">
                            <h3 class="mb-md-0 total-amount">Tổng tiền: <span id="total-amount"><?= number_format($totalAmount, 0, ',', '.') ?> đ</span></h3>
                            <a href="/checkout" class="btn btn-success btn-checkout">
                                <i class="bi bi-credit-card me-2"></i>Thanh toán
                            </a>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Bootstrap 5 JavaScript Bundle with Popper -->
    <script>
        $(document).ready(function() {
            // Xử lý cập nhật số lượng
            $(".update-quantity").on("change", function() {
                let cartId = $(this).data("cart-id");
                let quantity = $(this).val();
                let loading = $(this).siblings(".loading");

                loading.show(); // Hiển thị loading

                $.ajax({
                    url: "/cart/update",
                    type: "POST",
                    data: {
                        id: cartId,
                        quantity: quantity
                    },
                    success: function(response) {
                        location.reload(); // Refresh trang sau khi cập nhật thành công
                    },
                    error: function() {
                        alert("Cập nhật thất bại, vui lòng thử lại!");
                    },
                    complete: function() {
                        loading.hide(); // Ẩn loading
                    }
                });
            });

            // Xử lý xóa toàn bộ giỏ hàng
            $("#clear-cart").on("click", function() {
                if (confirm("Bạn có chắc chắn muốn xóa toàn bộ giỏ hàng?")) {
                    window.location.href = "/cart/delete-all";
                }
            });

            // Xử lý xóa từng sản phẩm
            $(".delete-item").on("click", function(e) {
                e.preventDefault();
                let cartId = $(this).data("cart-id");
                if (confirm("Bạn có chắc chắn muốn xóa sản phẩm này?")) {
                    window.location.href = "/cart/delete/" + cartId;
                }
            });
        });
    </script>
</body>
</html>