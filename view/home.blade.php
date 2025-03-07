<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trang chủ</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        .product-card {
            border: 1px solid #e0e0e0;
            border-radius: 15px;
            padding: 20px;
            background-color: #fff;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease-in-out, box-shadow 0.3s ease-in-out;
        }

        .product-card:hover {
            transform: scale(1.05);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.15);
        }

        .product-img {
            width: 100%;
            height: 250px;
            object-fit: cover;
            border-radius: 10px;
            transition: transform 0.3s ease-in-out;
        }

        .product-img:hover {
            transform: scale(1.1);
        }

        .product-name {
            font-weight: bold;
            font-size: 18px;
            color: #333;
            margin-top: 15px;
            text-overflow: ellipsis;
            white-space: nowrap;
            overflow: hidden;
        }

        .product-price {
            color: #e74c3c;
            font-size: 20px;
            font-weight: bold;
            margin-top: 10px;
        }

        .btn-info {
            background-color: #3498db;
            border-color: #3498db;
            color: white;
            transition: background-color 0.3s ease-in-out;
        }

        .btn-info:hover {
            background-color: #2980b9;
            border-color: #2980b9;
        }

        h2 {
            color: #2c3e50;
            font-size: 2.5rem;
            font-weight: 700;
            text-align: center;
            margin-bottom: 30px;
        }
    </style>
</head>
<body>
<?php if (!empty($products)): ?>
    <?php foreach ($products as $product): ?>
        <div class="col-md-3 mb-4">
            <div class="product-card">
                <a href="/products/<?= $product['id'] ?>">
                    <img src="http://localhost:8000/<?= htmlspecialchars($product['image_url']) ?>" 
                        alt="<?= htmlspecialchars($product['name']) ?>" class="product-img">    
                </a>
                <div class="product-info">
                    <h4 class="product-name"><?= htmlspecialchars($product['name']) ?></h4>
                    <p class="product-price"><?= number_format($product['price'], 0, ',', '.') ?> đ</p>
                    <a href="/products/<?= $product['id'] ?>" class="btn btn-info btn-sm">Mua ngay</a>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
<?php else: ?>
    <p class="text-center">Không có sản phẩm nào!</p>
<?php endif; ?>


</body>
</html>
