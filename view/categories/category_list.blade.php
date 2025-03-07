<div class="container mt-4">
    <h1>Categories</h1>
    <a href="/categories/create" class="btn btn-success mb-3">+ ADD</a>
    
    <table class="table table-bordered table-striped">
    <thead class="table-dark">
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Description</th>
            <th>Actions</th>
        </tr>
    </thead>

        <tbody>
            <?php foreach ($categories as $category): ?>
            <tr>
                <td><?php echo htmlspecialchars($category['id']); ?></td>
                <td><?php echo htmlspecialchars($category['name']); ?></td>
                <td><?php echo htmlspecialchars($category['description']); ?></td>
                <td>
                    <a href="/categories/<?php echo $category['id']; ?>" class="btn btn-info btn-sm">Xem</a>
                    <a href="/categories/edit/<?php echo $category['id']; ?>" class="btn btn-primary btn-sm">Sửa</a>
                    <a href="/categories/delete/<?php echo $category['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?');">Xoa</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>