<div class="container mt-4">
    <h1>Chỉnh Sửa Danh Mục</h1>
    <form method="POST">
        <div class="mb-3">
            <label for="name" class="form-label">Tên Danh Mục</label>
            <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($category['name']); ?>" required>
        </div>
        <div class="mb-3">
            <label for="description" class="form-label">Mô Tả</label>
            <textarea class="form-control" id="description" name="description" rows="4"><?php echo htmlspecialchars($category['description']); ?></textarea>
        </div>
        <button type="submit" class="btn btn-primary">Cập Nhật</button>
        <a href="/categories" class="btn btn-secondary">Hủy</a>
    </form>
</div>