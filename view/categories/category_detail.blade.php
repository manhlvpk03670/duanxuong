<div class="container mt-4">
    <h1>Chi Tiết Danh Mục</h1>
    <div class="card">
        <div class="card-body">
            <h5 class="card-title">Tên danh mục: <?php echo htmlspecialchars($category['name']); ?></h5>
            <p class="card-text"><strong>Mô tả:</strong> <?php echo htmlspecialchars($category['description']); ?></p>
            
            <?php if (isset($products) && !empty($products)): ?>
            <h5 class="mt-4">Sản phẩm trong danh mục:</h5>
            <ul class="list-group">
                <?php foreach ($products as $product): ?>
                <li class="list-group-item">
                    <?php echo htmlspecialchars($product['name']); ?> - 
                    <?php echo number_format($product['price'], 0, ',', '.'); ?> VNĐ
                </li>
                <?php endforeach; ?>
            </ul>
            <?php else: ?>
            <p class="text-muted">Chưa có sản phẩm nào trong danh mục này.</p>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="mt-3">
        <a href="/categories/edit/<?php echo $category['id']; ?>" class="btn btn-primary">Chỉnh Sửa</a>
        <a href="/categories" class="btn btn-secondary">Quay Lại</a>
    </div>
</div>