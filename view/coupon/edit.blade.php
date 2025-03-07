<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Chỉnh sửa Mã Giảm Giá</title>
</head>
<body>
    <h1>Chỉnh sửa Mã Giảm Giá</h1>
    <form action="/coupons/edit/<?= $coupon['id'] ?>" method="POST">
        <label for="code">Mã: </label>
        <input type="text" name="code" value="<?= htmlspecialchars($coupon['code']) ?>" required>
        <br>

        <label for="discount">Giảm Giá: </label>
        <input type="number" name="discount" value="<?= $coupon['discount'] ?>" step="0.01" required>
        <br>

        <label for="discount_type">Loại Giảm Giá: </label>
        <select name="discount_type" required>
            <option value="percentage" <?= $coupon['discount_type'] === 'percentage' ? 'selected' : '' ?>>Phần trăm (%)</option>
            <option value="fixed" <?= $coupon['discount_type'] === 'fixed' ? 'selected' : '' ?>>Số tiền cố định (VNĐ)</option>
        </select>
        <br>

        <label for="expiry_date">Hạn Sử Dụng: </label>
        <input type="date" name="expiry_date" value="<?= $coupon['expiry_date'] ?>" required>
        <br>

        <button type="submit">Cập nhật</button>
    </form>
    <a href="/coupons">Quay lại danh sách</a>
</body>
</html>
