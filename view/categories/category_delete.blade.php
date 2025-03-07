<div class="container mt-4">
    <h1>Xóa Danh Mục</h1>
    <div class="alert alert-danger">
        <h4>Bạn có chắc chắn muốn xóa danh mục này?</h4>
        <p>Tên danh mục: <?php echo htmlspecialchars($category['name']); ?></p>
        <p>Mô tả: <?php echo htmlspecialchars($category['description']); ?></p>
    </div>
    
    <form method="POST">
        <button type="submit" class="btn btn-danger">Có, Xóa Danh Mục</button>
        <a href="/categories" class="btn btn-secondary">Hủy</a>
    </form>
</div>