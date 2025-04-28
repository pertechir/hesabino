<?php
$page = 'list-products';
require_once 'includes/header.php';
$pageTitle = 'لیست محصولات';

// دریافت لیست محصولات از دیتابیس
try {
    $products = $pdo->query("SELECT * FROM products")->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $error_message = "خطا در دریافت لیست محصولات: " . $e->getMessage();
    error_log("Error fetching products: " . $e->getMessage());
}
?>

<!-- اضافه کردن استایل‌های DataTables -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">

<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">لیست محصولات</h5>
                    <a href="add-product.php" class="btn btn-outline-light">
                        <i class="bi bi-plus-circle"></i> افزودن محصول جدید
                    </a>
                </div>
                <div class="card-body">
                    <?php if (!empty($products)) : ?>
                        <div class="table-responsive">
                            <table id="productTable" class="table table-bordered table-hover table-striped align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>#</th>
                                        <th>نام محصول</th>
                                        <th>کد حسابداری</th>
                                        <th>بارکد</th>
                                        <th>دسته‌بندی</th>
                                        <th>قیمت فروش (ریال)</th>
                                        <th>قیمت خرید (ریال)</th>
                                        <th>واحد اصلی</th>
                                        <th>موجودی فعلی</th>
                                        <th>نقطه سفارش</th>
                                        <th>مالیات فروش (%)</th>
                                        <th>مالیات خرید (%)</th>
                                        <th>عملیات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($products as $index => $product) : ?>
                                        <tr>
                                            <td><?= $index + 1 ?></td>
                                            <td><?= htmlspecialchars($product['name']) ?></td>
                                            <td><?= htmlspecialchars($product['accounting_code']) ?></td>
                                            <td><?= htmlspecialchars($product['barcode']) ?></td>
                                            <td><?= htmlspecialchars($product['category_id']) ?></td>
                                            <td><?= number_format($product['sales_price']) ?></td>
                                            <td><?= number_format($product['purchase_price']) ?></td>
                                            <td><?= htmlspecialchars($product['main_unit']) ?></td>
                                            <td><?= number_format($product['current_stock']) ?></td>
                                            <td><?= number_format($product['reorder_point']) ?></td>
                                            <td><?= $product['sales_tax'] ?></td>
                                            <td><?= $product['purchase_tax'] ?></td>
                                            <td>
                                                <a href="edit-product.php?id=<?= $product['id'] ?>" class="btn btn-sm btn-warning">
                                                    <i class="bi bi-pencil-square"></i> ویرایش
                                                </a>
                                                <button class="btn btn-sm btn-danger" onclick="deleteProduct(<?= $product['id'] ?>)">
                                                    <i class="bi bi-trash"></i> حذف
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else : ?>
                        <div class="alert alert-warning text-center">
                            هیچ محصولی یافت نشد.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- اضافه کردن اسکریپت‌های DataTables -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>

<script>
    $(document).ready(function () {
        $('#productTable').DataTable({
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.13.4/i18n/fa.json"
            },
            "pageLength": 10,
            "ordering": true,
            "searching": true
        });
    });

    function deleteProduct(productId) {
        if (confirm("آیا از حذف این محصول مطمئن هستید؟")) {
            // ارسال درخواست حذف به سرور
            window.location.href = `delete-product.php?id=${productId}`;
        }
    }
</script>