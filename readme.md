php -S localhost:8000

                    <!-- Ảnh phụ của sản phẩm -->
                    <div class="sub-images mt-3">
                        <h5>Ảnh phụ:</h5>
                        <div class="row">
                            <?php if (!empty($subImages)): ?>
                                <?php foreach ($subImages as $subImage): ?>
                                    <div class="col-md-3 mb-3">
                                        <img src="http://localhost:8000/uploads/sub_images/<?= htmlspecialchars($subImage['image']) ?>"
                                            class="img-fluid sub-product-image"
                                            alt="Sub image"
                                            onclick="changeMainImage(this.src)">
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p>Không có ảnh phụ cho sản phẩm này.</p> <!-- Hiển thị khi không có ảnh phụ -->
                            <?php endif; ?>
                        </div>
                    </div>