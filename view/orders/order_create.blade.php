<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tạo đơn hàng mới</title>
    <!-- Bootstrap CSS -->
    <style>
        .card {
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        }
        .page-header {
            padding-bottom: 0.5rem;
            margin-bottom: 1.5rem;
            border-bottom: 1px solid #dee2e6;
        }
    </style>
</head>
<body class="bg-light">
    <div class="container py-4">
        <div class="page-header">
            <h2>Tạo đơn hàng mới</h2>
        </div>
        
        <!-- Form tạo đơn hàng -->
        <div class="card">
            <div class="card-body">
                <form action="/orders/create" method="POST">
                    <div class="mb-3">
                        <label for="user_id" class="form-label">Người dùng:</label>
                        <select name="user_id" class="form-select">
                            <?php foreach ($users as $user): ?>
                            <option value="<?= $user['id'] ?>"><?= $user['name'] ?> (<?= $user['email'] ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="total_price" class="form-label">Tổng tiền:</label>
                        <input type="number" name="total_price" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label for="payment_method" class="form-label">Phương thức thanh toán:</label>
                        <select name="payment_method" class="form-select">
                            <option value="COD">COD</option>
                            <option value="Chuyển khoản">Chuyển khoản</option>
                            <option value="VNPay">VNPay</option>
                        </select>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="/orders" class="btn btn-secondary">Quay lại</a>
                        <button type="submit" class="btn btn-primary">Tạo đơn hàng</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS Bundle with Popper -->
</body>
</html>