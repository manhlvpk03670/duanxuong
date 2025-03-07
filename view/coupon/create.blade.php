<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Thêm Mã Giảm Giá</title>
</head>
<body>
    <h1>Thêm Mã Giảm Giá</h1>
    <form action="/coupons/create" method="POST">
        <label for="code">Mã: </label>
        <input type="text" name="code" required>
        <br>

        <label for="discount">Giảm Giá: </label>
        <input type="number" name="discount" step="0.01" required>
        <br>

        <label for="discount_type">Loại Giảm Giá: </label>
        <select name="discount_type" required>
            <option value="percentage">Phần trăm (%)</option>
            <option value="fixed">Số tiền cố định (VNĐ)</option>
        </select>
        <br>

        <label for="expiry_date">Hạn Sử Dụng: </label>
        <input type="date" name="expiry_date" required>
        <br>

        <button type="submit">Thêm</button>
    </form>
    <a href="/coupons">Quay lại danh sách</a>
</body>
</html>