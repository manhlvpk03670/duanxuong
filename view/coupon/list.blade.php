<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Danh Sách Mã Giảm Giá</title>
</head>
<body>
    <h1>Danh Sách Mã Giảm Giá</h1>
    <a href="/coupons/create">Thêm Mã Giảm Giá</a>
    <table border="1">
        <thead>
            <tr>
                <th>ID</th>
                <th>Mã</th>
                <th>Giảm Giá (%)</th>
                <th>Hạn Sử Dụng</th>
                <th>Hành Động</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($coupons as $coupon): ?>
            <tr>
                <td><?= $coupon['id'] ?></td>
                <td><?= $coupon['code'] ?></td>
                <td><?= $coupon['discount'] ?>%</td>
                <td><?= $coupon['expiry_date'] ?></td>
                <td>
                    <a href="/coupons/<?= $coupon['id'] ?>">Xem</a> |
                    <a href="/coupons/edit/<?= $coupon['id'] ?>">Sửa</a> |
                    <a href="/coupons/delete/<?= $coupon['id'] ?>" onclick="return confirm('Bạn có chắc muốn xóa?')">Xóa</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>
