<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Chi Tiết Mã Giảm Giá</title>
</head>
<body>
    <h1>Chi Tiết Mã Giảm Giá</h1>
    <p><strong>ID:</strong> <?= $coupon['id'] ?></p>
    <p><strong>Mã:</strong> <?= $coupon['code'] ?></p>
    <p><strong>Giảm Giá:</strong> <?= $coupon['discount'] ?>%</p>
    <p><strong>Hạn Sử Dụng:</strong> <?= $coupon['expiry_date'] ?></p>
    <a href="/coupons">Quay lại danh sách</a>
</body>
</html>
